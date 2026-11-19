<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\Income;
use App\Models\Planting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmRexTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'farmer'): User
    {
        return User::create(['name' => 'Test User', 'email' => $role.'@example.com', 'role' => $role, 'password' => 'password']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_with_valid_and_invalid_credentials(): void
    {
        $u = $this->user();

        $this->post('/login', ['email' => $u->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertRedirect('/dashboard');
    }

    public function test_registering_sends_you_to_sign_in_without_logging_you_in(): void
    {
        $this->post('/register', [
            'name' => 'New Farmer', 'email' => 'new@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/login');

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'admin']); // first account

        $this->post('/register', [
            'name' => 'Second', 'email' => 'second@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'second@example.com', 'role' => 'farmer']);
    }

    public function test_first_sign_in_says_welcome_and_later_ones_say_welcome_back(): void
    {
        $u = $this->user();

        $this->post('/login', ['email' => $u->email, 'password' => 'password'])
            ->assertSessionHas('success', fn ($m) => str_starts_with($m, 'Welcome to FarmRex'));
        $this->post('/logout');

        $this->post('/login', ['email' => $u->email, 'password' => 'password'])
            ->assertSessionHas('success', fn ($m) => str_starts_with($m, 'Welcome back'));
    }

    public function test_inactive_accounts_cannot_sign_in(): void
    {
        $u = $this->user();
        $u->update(['is_active' => false]);

        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_activity_form_points_to_planting_when_none_exist(): void
    {
        $this->actingAs($this->user())->get('/activities/create')
            ->assertOk()->assertSee('Record a planting first');
    }

    public function test_dashboard_loads(): void
    {
        $this->actingAs($this->user())->get('/dashboard')->assertOk()->assertSee('Income Summary');
    }

    public function test_income_amount_is_calculated_from_quantity_and_price(): void
    {
        $this->actingAs($this->user())->post('/incomes', [
            'category' => 'Crop Sales', 'income_date' => now()->toDateString(),
            'produce' => 'Beans', 'quantity' => 10, 'unit' => 'kg', 'unit_price' => 3000,
        ])->assertRedirect('/incomes');

        $this->assertEquals(30000, Income::first()->amount);
    }

    public function test_income_requires_an_amount(): void
    {
        $this->actingAs($this->user())->post('/incomes', [
            'category' => 'Crop Sales', 'income_date' => now()->toDateString(),
        ])->assertSessionHasErrors('amount');
    }

    public function test_planting_gets_expected_harvest_date_and_reminder(): void
    {
        $user = $this->user();
        $farm = Farm::create(['name' => 'Field A', 'user_id' => $user->id]);
        $crop = Crop::create(['name' => 'Maize', 'days_to_maturity' => 100, 'user_id' => $user->id]);

        $this->actingAs($user)->post('/plantings', [
            'crop_id' => $crop->id, 'farm_id' => $farm->id, 'planting_date' => '2026-03-01',
            'growth_stage' => 'Planted', 'status' => 'Active',
        ])->assertRedirect('/plantings');

        $p = Planting::first();
        $this->assertEquals('2026-06-09', $p->expected_harvest_date->toDateString());
        $this->assertDatabaseHas('reminders', ['planting_id' => $p->id, 'reminder_type' => 'Harvest']);
    }

    public function test_farmers_cannot_open_user_admin_or_reports(): void
    {
        $farmer = $this->user('farmer');
        $admin = $this->user('admin');

        $this->actingAs($farmer)->get('/users')->assertForbidden();
        $this->actingAs($farmer)->get('/reports')->assertForbidden();
        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/reports')->assertOk();
    }

    public function test_farmers_only_see_and_edit_their_own_records(): void
    {
        $owner = $this->user('farmer');
        $other = User::create(['name' => 'Other', 'email' => 'other@example.com', 'role' => 'farmer', 'password' => 'password']);
        $income = Income::create(['category' => 'Other Income', 'amount' => 1000, 'income_date' => now(), 'user_id' => $owner->id]);

        $this->actingAs($other)->get("/incomes/{$income->id}/edit")->assertNotFound();
        $this->actingAs($other)->get('/incomes')->assertDontSee('Other Income');
        $this->actingAs($owner)->get("/incomes/{$income->id}/edit")->assertOk();
    }

    public function test_admin_sees_all_farmers_records_and_can_manage_them(): void
    {
        $farmer = $this->user('farmer');
        $income = Income::create(['category' => 'Other Income', 'amount' => 1000, 'income_date' => now(), 'user_id' => $farmer->id]);
        $admin = $this->user('admin');

        $this->actingAs($admin)->get('/incomes')->assertOk()->assertSee('Other Income')->assertSee($farmer->name);
        $this->actingAs($admin)->get("/incomes/{$income->id}/edit")->assertOk();
        $this->actingAs($admin)->delete("/incomes/{$income->id}")->assertRedirect('/incomes');
        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }

    public function test_admin_can_create_a_farmer_account(): void
    {
        $this->actingAs($this->user('admin'))->post('/users', [
            'name' => 'Jane Farmer', 'email' => 'jane@example.com', 'role' => 'farmer',
            'is_active' => 1, 'password' => 'password123',
        ])->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'role' => 'farmer', 'is_active' => 1]);
    }
}

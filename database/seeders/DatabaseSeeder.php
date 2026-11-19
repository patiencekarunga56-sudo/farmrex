<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Crop;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\FarmInput;
use App\Models\Harvest;
use App\Models\Income;
use App\Models\LabourRecord;
use App\Models\Observation;
use App\Models\Planting;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Users (password for all: "password") ----
        User::create(['name' => 'Emma Onoh', 'email' => 'admin@farmrex.test', 'phone' => '0700000001', 'role' => 'admin', 'password' => 'password']);
        User::create(['name' => 'Grace Tumusiime', 'email' => 'grace@farmrex.test', 'phone' => '0700000002', 'role' => 'farmer', 'password' => 'password']);
        $mem = User::create(['name' => 'Moses Byaruhanga', 'email' => 'farmer@farmrex.test', 'phone' => '0700000003', 'role' => 'farmer', 'password' => 'password']);
        // The demo records below belong to the farmer Moses, so signing in as him shows a
        // populated system. The admin sees every farmer's records; Grace starts empty.
        $u = fn () => $mem->id;

        // ---- Fields ----
        $fa = Farm::create(['name' => 'Field A – Lower Garden', 'location' => 'Butiiti, Kyenjojo', 'size_acres' => 2.5, 'soil_type' => 'Loam', 'user_id' => $mem->id]);
        $fb = Farm::create(['name' => 'Field B – Hill Plot', 'location' => 'Butiiti, Kyenjojo', 'size_acres' => 1.5, 'soil_type' => 'Sandy loam', 'user_id' => $mem->id]);
        $fc = Farm::create(['name' => 'Field C – Valley Garden', 'location' => 'Butiiti, Kyenjojo', 'size_acres' => 3, 'soil_type' => 'Clay loam', 'user_id' => $mem->id]);

        // ---- Crops ----
        $maize = Crop::create(['name' => 'Maize', 'variety' => 'Longe 5', 'category' => 'Cereal', 'days_to_maturity' => 110, 'user_id' => $mem->id]);
        $beans = Crop::create(['name' => 'Beans', 'variety' => 'NABE 15', 'category' => 'Legume', 'days_to_maturity' => 75, 'user_id' => $mem->id]);
        $tomato = Crop::create(['name' => 'Tomatoes', 'variety' => 'Tengeru', 'category' => 'Vegetable', 'days_to_maturity' => 90, 'user_id' => $mem->id]);
        $matooke = Crop::create(['name' => 'Bananas (Matooke)', 'variety' => 'Mbwazirume', 'category' => 'Fruit', 'days_to_maturity' => 300, 'user_id' => $mem->id]);
        $g = Crop::create(['name' => 'Groundnuts', 'variety' => 'Serenut 5R', 'category' => 'Legume', 'days_to_maturity' => 100, 'user_id' => $mem->id]);
        Crop::create(['name' => 'Cassava', 'variety' => 'NAROCASS 1', 'category' => 'Root & tuber', 'days_to_maturity' => 300, 'user_id' => $mem->id]);

        // ---- Plantings ----
        $mk = function ($crop, $farm, $daysAgo, $stage, $status, $acres, $notes = null) use ($mem) {
            $planted = now()->subDays($daysAgo);

            return Planting::create([
                'crop_id' => $crop->id, 'farm_id' => $farm->id, 'planting_date' => $planted,
                'expected_harvest_date' => $planted->copy()->addDays($crop->days_to_maturity),
                'area_acres' => $acres, 'growth_stage' => $stage, 'status' => $status, 'notes' => $notes, 'user_id' => $mem->id,
            ]);
        };
        $p1 = $mk($maize, $fa, 75, 'Flowering', 'Active', 1.5, 'Second season maize, planted in rows 75cm apart.');
        $p2 = $mk($beans, $fb, 62, 'Fruiting', 'Active', 1);
        $p3 = $mk($tomato, $fc, 48, 'Vegetative', 'Active', 0.5, 'Staked and mulched.');
        $p4 = $mk($matooke, $fc, 200, 'Fruiting', 'Active', 1.5);
        $p5 = $mk($g, $fb, 150, 'Harvested', 'Completed', 0.5);
        $p6 = $mk($beans, $fa, 130, 'Harvested', 'Completed', 1);

        // ---- Activities ----
        $acts = [
            [$p1, 'Land preparation', 80, 'Ploughed and harrowed the plot.'],
            [$p1, 'Planting', 75, 'Planted with a spacing of 75 x 30 cm.'],
            [$p1, 'Weeding', 50, 'First weeding by hand hoe.'],
            [$p1, 'Fertiliser application', 40, 'Top-dressed with urea.'],
            [$p2, 'Planting', 62, 'Drilled seed in rows.'],
            [$p2, 'Weeding', 35, 'Weeded and thinned.'],
            [$p3, 'Planting', 48, 'Transplanted seedlings from the nursery bed.'],
            [$p3, 'Spraying', 20, 'Sprayed against blight.'],
            [$p4, 'Pruning / thinning', 12, 'Removed extra suckers and dry leaves.'],
            [$p3, 'Irrigation', 3, 'Watered by bucket during dry spell.'],
        ];
        foreach ($acts as [$p, $type, $ago, $desc]) {
            Activity::create(['planting_id' => $p->id, 'activity_type' => $type, 'activity_date' => now()->subDays($ago), 'description' => $desc, 'performed_by' => 'Group members', 'user_id' => $u()]);
        }

        // ---- Farm inputs ----
        $inputs = [
            ['Longe 5 maize seed', 'Seed', 15, 'kg', 7000, 78, 'Kyenjojo Agro Vet', $p1],
            ['NPK 15:15:15', 'Fertiliser', 100, 'kg', 4200, 70, 'Fort Portal Agro Supplies', $p1],
            ['Urea', 'Fertiliser', 50, 'kg', 3800, 42, 'Fort Portal Agro Supplies', $p1],
            ['NABE 15 bean seed', 'Seed', 40, 'kg', 5500, 63, 'Kyenjojo Agro Vet', $p2],
            ['Tomato seed (Tengeru)', 'Seed', 2, 'packets', 35000, 55, 'Kyenjojo Agro Vet', $p3],
            ['Ridomil fungicide', 'Pesticide', 4, 'packets', 18000, 22, 'Kyenjojo Agro Vet', $p3],
            ['Karate pesticide', 'Pesticide', 2, 'bottles', 24000, 14, 'Kyenjojo Agro Vet', $p3],
            ['Hand hoes', 'Tools & equipment', 6, 'pieces', 15000, 90, 'Butiiti Market', null],
        ];
        foreach ($inputs as [$n, $t, $q, $unit, $cost, $ago, $sup2, $p]) {
            FarmInput::create(['name' => $n, 'type' => $t, 'quantity' => $q, 'unit' => $unit, 'unit_cost' => $cost, 'total_cost' => $q * $cost, 'purchase_date' => now()->subDays($ago), 'supplier' => $sup2, 'planting_id' => $p?->id, 'user_id' => $u()]);
        }

        // ---- Observations ----
        $obs = [
            [$p1, 55, 'Growth stage update', null, 'Vegetative', 'Crop is knee-high and healthy.', null],
            [$p1, 30, 'Pest', 'Medium', null, 'Fall armyworm damage on about 10% of plants.', 'Sprayed with recommended pesticide.'],
            [$p1, 8, 'Growth stage update', null, 'Flowering', 'Tasselling has started.', null],
            [$p2, 20, 'Disease', 'Low', null, 'Few leaves showing angular leaf spot.', 'Removed affected leaves.'],
            [$p3, 18, 'Disease', 'High', null, 'Early blight spotted on lower leaves.', 'Sprayed fungicide; improved spacing.'],
            [$p4, 25, 'Growth stage update', null, 'Fruiting', 'Bunches emerging on mature stems.', null],
        ];
        foreach ($obs as [$p, $ago, $cat, $sev, $stage, $desc, $act]) {
            Observation::create(['planting_id' => $p->id, 'observed_on' => now()->subDays($ago), 'category' => $cat, 'severity' => $sev, 'growth_stage' => $stage, 'description' => $desc, 'action_taken' => $act, 'user_id' => $u()]);
        }

        // ---- Harvests ----
        Harvest::create(['planting_id' => $p5->id, 'harvest_date' => now()->subDays(45), 'quantity' => 420, 'unit' => 'kg', 'quality' => 'Grade A', 'storage_location' => 'Group store', 'user_id' => $mem->id]);
        Harvest::create(['planting_id' => $p6->id, 'harvest_date' => now()->subDays(60), 'quantity' => 650, 'unit' => 'kg', 'quality' => 'Grade A', 'storage_location' => 'Group store', 'user_id' => $mem->id]);
        Harvest::create(['planting_id' => $p4->id, 'harvest_date' => now()->subDays(6), 'quantity' => 18, 'unit' => 'bunches', 'quality' => 'Grade B', 'storage_location' => 'Sold fresh', 'user_id' => $mem->id]);

        // ---- Labour ----
        $labour = [
            ['Joseph Kato', 'Land preparation', 80, 3, 10000, $p1],
            ['Sarah Nyakato', 'Planting', 75, 2, 8000, $p1],
            ['Joseph Kato', 'Weeding', 50, 2, 10000, $p1],
            ['Amos Mugisha', 'Spraying', 20, 1, 12000, $p3],
            ['Sarah Nyakato', 'Weeding', 35, 2, 8000, $p2],
            ['Amos Mugisha', 'Harvesting', 6, 1, 12000, $p4],
        ];
        foreach ($labour as [$w, $task, $ago, $days, $rate, $p]) {
            LabourRecord::create(['worker_name' => $w, 'task' => $task, 'work_date' => now()->subDays($ago), 'days_worked' => $days, 'rate_per_day' => $rate, 'amount' => $days * $rate, 'planting_id' => $p->id, 'user_id' => $u()]);
        }

        // ---- Expenses (other than inputs & labour, which are counted automatically) ----
        $exp = [
            ['Transport', 120000, 3, 'Boda boda to Fort Portal for inputs', 'Boda Riders Assoc.', $p1],
            ['Storage & packaging', 85000, 30, 'Sacks and tarpaulins', 'Butiiti Market', null],
            ['Equipment & tools', 150000, 70, 'Knapsack sprayer', 'Kyenjojo Agro Vet', null],
            ['Utilities & airtime', 20000, 12, 'Airtime for coordination', null, null],
            ['Land rent / lease', 300000, 100, 'Lease of Field C for the season', 'Mr. Tusiime', null],
            ['Transport', 60000, 48, 'Transporting beans to market', null, $p6],
            ['Repairs & maintenance', 45000, 140, 'Store roof repair', null, null],
        ];
        foreach ($exp as [$cat, $amt, $ago, $desc, $to, $p]) {
            Expense::create(['category' => $cat, 'amount' => $amt, 'expense_date' => now()->subDays($ago), 'description' => $desc, 'paid_to' => $to, 'planting_id' => $p?->id, 'user_id' => $u()]);
        }

        // ---- Income ----
        $inc = [
            ['Crop Sales', 'Groundnuts', 420, 'kg', 4500, 40, 'Kyenjojo Traders', $p5],
            ['Crop Sales', 'Beans', 650, 'kg', 3000, 55, 'Fort Portal Market', $p6],
            ['Crop Sales', 'Matooke', 18, 'bunches', 15000, 5, 'Butiiti Trading Centre', $p4],
            ['Grants & Support', null, null, null, null, 95, 'Youth Livelihood Programme', null, 1500000],
            ['Member Contributions', null, null, null, null, 20, 'Group members', null, 400000],
            ['Asset Sales', 'Old wheelbarrow', 1, 'pieces', 60000, 15, 'Local farmer', null],
            ['Processed Produce', 'Groundnut paste', 30, 'bottles', 8000, 33, 'Local shops', $p5],
            ['Crop Sales', 'Tomatoes', 120, 'kg', 2500, 2, 'Butiiti Market', $p3],
        ];
        foreach ($inc as $r) {
            [$cat, $prod, $q, $unit, $price, $ago, $buyer, $p] = $r;
            $amount = $r[8] ?? ($q * $price);
            Income::create(['category' => $cat, 'produce' => $prod, 'quantity' => $q, 'unit' => $unit, 'unit_price' => $price, 'amount' => $amount, 'buyer' => $buyer, 'income_date' => now()->subDays($ago), 'description' => $prod ? 'Sold '.$prod : $cat, 'planting_id' => $p?->id, 'user_id' => $u()]);
        }

        // ---- Reminders ----
        foreach ([$p1, $p2, $p3, $p4] as $p) {
            Reminder::create(['title' => 'Harvest due: '.$p->crop->name.' ('.$p->farm->name.')', 'reminder_type' => 'Harvest', 'due_date' => $p->expected_harvest_date, 'status' => 'Pending', 'planting_id' => $p->id, 'user_id' => $mem->id]);
        }
        Reminder::create(['title' => 'Second weeding – Field A maize', 'reminder_type' => 'Weeding', 'due_date' => now()->subDays(1), 'status' => 'Pending', 'planting_id' => $p1->id, 'user_id' => $mem->id]);
        Reminder::create(['title' => 'Spray tomatoes against blight', 'reminder_type' => 'Spraying', 'due_date' => now()->addDays(2), 'status' => 'Pending', 'planting_id' => $p3->id, 'user_id' => $mem->id]);
        Reminder::create(['title' => 'Monthly group meeting', 'reminder_type' => 'Meeting', 'due_date' => now()->addDays(6), 'status' => 'Pending', 'user_id' => $mem->id]);
        Reminder::create(['title' => 'Buy fertiliser for next season', 'reminder_type' => 'Fertiliser', 'due_date' => now()->addDays(14), 'status' => 'Pending', 'user_id' => $mem->id]);
        Reminder::create(['title' => 'Prune banana plantation', 'reminder_type' => 'Other', 'due_date' => now()->subDays(10), 'status' => 'Done', 'planting_id' => $p4->id, 'user_id' => $mem->id]);
    }
}

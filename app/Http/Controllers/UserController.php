<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class UserController extends CrudController
{
    protected string $model = User::class;
    protected string $route = 'users';
    protected string $title = 'Farmers & Users';
    protected string $singular = 'user';
    protected string $subtitle = 'Create and manage farmer accounts, and control who can access the system.';
    protected array $search = ['name', 'email', 'phone', 'role'];
    protected string $orderBy = 'name';
    protected string $orderDir = 'asc';
    protected bool $tracksUser = false;
    protected bool $ownerRestricted = false;

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Full name', 'rules' => 'required|string|max:255', 'col' => 6],
            'email' => ['label' => 'Email', 'type' => 'email', 'rules' => 'required|email', 'col' => 6],
            'phone' => ['label' => 'Phone', 'rules' => 'nullable|string|max:30', 'col' => 6],
            'role' => ['label' => 'Role', 'type' => 'select', 'rules' => 'required', 'options' => User::ROLES, 'default' => 'farmer', 'col' => 6],
            'is_active' => ['label' => 'Account status', 'type' => 'select', 'rules' => 'required',
                'options' => [1 => 'Active', 0 => 'Inactive'], 'default' => 1, 'col' => 6,
                'hint' => 'Inactive accounts cannot sign in.'],
            'password' => ['label' => 'Password', 'type' => 'password', 'rules' => 'nullable|min:8', 'col' => 6,
                'hint' => 'Leave blank when editing to keep the current password.'],
        ];
    }

    protected function rules(?Model $item = null): array
    {
        return array_merge(parent::rules($item), [
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($item?->id)],
            'password' => $item ? ['nullable', 'min:8'] : ['required', 'min:8'],
        ]);
    }

    protected function prepare(array $data, ?Model $item): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // An administrator cannot lock themselves out or demote themselves.
        if ($item && (int) $item->id === (int) auth()->id()) {
            $data['role'] = 'admin';
            $data['is_active'] = true;
        }

        return $data;
    }

    public function destroy($id)
    {
        if ((int) $id === (int) auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        return parent::destroy($id);
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Name', 'value' => fn ($m) => $m->name],
            ['label' => 'Email', 'value' => fn ($m) => $m->email],
            ['label' => 'Phone', 'value' => fn ($m) => $m->phone ?: '—'],
            ['label' => 'Role', 'value' => fn ($m) => $m->role_label, 'type' => 'badge'],
            ['label' => 'Status', 'value' => fn ($m) => $m->is_active ? 'Active' : 'Inactive', 'type' => 'badge'],
            ['label' => 'Last sign-in', 'value' => fn ($m) => $m->last_login_at, 'type' => 'datetime'],
        ];
    }
}

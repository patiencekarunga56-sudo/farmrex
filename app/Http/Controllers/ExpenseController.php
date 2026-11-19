<?php

namespace App\Http\Controllers;

use App\Models\Expense;

class ExpenseController extends CrudController
{
    protected string $model = Expense::class;
    protected string $route = 'expenses';
    protected string $title = 'Expenses';
    protected string $singular = 'expense';
    protected string $subtitle = 'Transport, equipment, storage and other farm costs.';
    protected string $hint = 'Seeds, fertiliser and pesticides go under Farm Inputs, and wages under Labour. They are counted here automatically, so do not enter them twice.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['category', 'paid_to', 'description'];
    protected string $orderBy = 'expense_date';

    protected function fields(): array
    {
        return [
            'category' => ['label' => 'Category', 'type' => 'select', 'rules' => 'required',
                'options' => $this->enum(['Transport', 'Equipment & tools', 'Storage & packaging', 'Utilities & airtime', 'Land rent / lease', 'Repairs & maintenance', 'Other expenses']), 'col' => 6],
            'expense_date' => ['label' => 'Date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 6],
            'amount' => ['label' => 'Amount (UGX)', 'type' => 'money', 'rules' => 'required|numeric|min:0', 'col' => 6],
            'paid_to' => ['label' => 'Paid to', 'rules' => 'nullable|string|max:255', 'col' => 6],
            'planting_id' => $this->plantingField('Related planting (optional)'),
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Date', 'value' => fn ($m) => $m->expense_date, 'type' => 'date'],
            ['label' => 'Category', 'value' => fn ($m) => $m->category],
            ['label' => 'Details', 'value' => fn ($m) => $m->description ?: ($m->paid_to ?: '—')],
            ['label' => 'Paid to', 'value' => fn ($m) => $m->paid_to ?: '—'],
            ['label' => 'Amount', 'value' => fn ($m) => $m->amount, 'type' => 'money'],
        ];
    }
}

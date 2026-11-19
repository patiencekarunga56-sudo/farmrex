<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class IncomeController extends CrudController
{
    public const CATEGORIES = ['Crop Sales', 'Processed Produce', 'Grants & Support', 'Member Contributions', 'Asset Sales', 'Other Income'];

    protected string $model = Income::class;
    protected string $route = 'incomes';
    protected string $title = 'Income';
    protected string $singular = 'income entry';
    protected string $subtitle = 'Sales of produce, grants, contributions and other money received.';
    protected string $hint = 'For produce sales, enter the quantity and price per unit and the total is filled in for you.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['category', 'produce', 'buyer', 'description'];
    protected string $orderBy = 'income_date';

    protected function fields(): array
    {
        return [
            'category' => ['label' => 'Category', 'type' => 'select', 'rules' => 'required', 'options' => $this->enum(self::CATEGORIES), 'col' => 6],
            'income_date' => ['label' => 'Date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 6],
            'produce' => ['label' => 'Produce sold', 'rules' => 'nullable|string|max:255', 'col' => 6, 'placeholder' => 'e.g. Tomatoes'],
            'buyer' => ['label' => 'Buyer / source', 'rules' => 'nullable|string|max:255', 'col' => 6],
            'quantity' => ['label' => 'Quantity', 'type' => 'number', 'step' => '0.01', 'rules' => 'nullable|numeric|min:0', 'col' => 3],
            'unit' => ['label' => 'Unit', 'type' => 'select', 'rules' => 'nullable',
                'options' => $this->enum(['kg', 'bags', 'sacks', 'tonnes', 'bunches', 'crates', 'pieces']), 'col' => 3],
            'unit_price' => ['label' => 'Price per unit (UGX)', 'type' => 'money', 'rules' => 'nullable|numeric|min:0', 'col' => 3],
            'amount' => ['label' => 'Total amount (UGX)', 'type' => 'money', 'rules' => 'nullable|numeric|min:0', 'col' => 3],
            'planting_id' => $this->plantingField('From planting (optional)'),
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function prepare(array $data, ?Model $item): array
    {
        if (($data['amount'] ?? null) === null && ! empty($data['quantity']) && ! empty($data['unit_price'])) {
            $data['amount'] = round((float) $data['quantity'] * (float) $data['unit_price'], 2);
        }

        if (($data['amount'] ?? null) === null) {
            throw ValidationException::withMessages([
                'amount' => 'Enter the total amount, or the quantity and price per unit.',
            ]);
        }

        return $data;
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Date', 'value' => fn ($m) => $m->income_date, 'type' => 'date'],
            ['label' => 'Category', 'value' => fn ($m) => $m->category],
            ['label' => 'Details', 'value' => fn ($m) => $m->description ?: ($m->produce ?: '—')],
            ['label' => 'Buyer / source', 'value' => fn ($m) => $m->buyer ?: '—'],
            ['label' => 'Amount', 'value' => fn ($m) => $m->amount, 'type' => 'money'],
        ];
    }
}

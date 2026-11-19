<?php

namespace App\Http\Controllers;

use App\Models\FarmInput;
use Illuminate\Database\Eloquent\Model;

class FarmInputController extends CrudController
{
    protected string $model = FarmInput::class;
    protected string $route = 'inputs';
    protected string $title = 'Farm Inputs';
    protected string $singular = 'input';
    protected string $subtitle = 'Seeds, fertilisers, pesticides and other materials bought or used.';
    protected string $hint = 'The total cost is worked out for you and counted automatically under Expenses.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['name', 'type', 'supplier'];
    protected string $orderBy = 'purchase_date';

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Item', 'rules' => 'required|string|max:255', 'col' => 6, 'placeholder' => 'e.g. NPK 15:15:15'],
            'type' => ['label' => 'Type', 'type' => 'select', 'rules' => 'required',
                'options' => $this->enum(['Seed', 'Fertiliser', 'Pesticide', 'Herbicide', 'Tools & equipment', 'Other']), 'col' => 6],
            'quantity' => ['label' => 'Quantity', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|numeric|min:0', 'col' => 4],
            'unit' => ['label' => 'Unit', 'type' => 'select', 'rules' => 'nullable',
                'options' => $this->enum(['kg', 'g', 'litres', 'bags', 'packets', 'bottles', 'pieces']), 'col' => 4],
            'unit_cost' => ['label' => 'Cost per unit (UGX)', 'type' => 'money', 'rules' => 'required|numeric|min:0', 'col' => 4],
            'purchase_date' => ['label' => 'Date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 6],
            'supplier' => ['label' => 'Supplier', 'rules' => 'nullable|string|max:255', 'col' => 6],
            'planting_id' => $this->plantingField('Used on planting (optional)'),
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function prepare(array $data, ?Model $item): array
    {
        $data['total_cost'] = round(((float) $data['quantity']) * ((float) $data['unit_cost']), 2);

        return $data;
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Date', 'value' => fn ($m) => $m->purchase_date, 'type' => 'date'],
            ['label' => 'Item', 'value' => fn ($m) => $m->name],
            ['label' => 'Type', 'value' => fn ($m) => $m->type, 'type' => 'badge'],
            ['label' => 'Quantity', 'value' => fn ($m) => rtrim(rtrim(number_format($m->quantity, 2), '0'), '.').' '.$m->unit, 'type' => 'number'],
            ['label' => 'Total cost', 'value' => fn ($m) => $m->total_cost, 'type' => 'money'],
        ];
    }
}

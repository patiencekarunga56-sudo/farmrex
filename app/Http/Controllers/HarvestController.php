<?php

namespace App\Http\Controllers;

use App\Models\Harvest;

class HarvestController extends CrudController
{
    protected string $model = Harvest::class;
    protected string $route = 'harvests';
    protected string $title = 'Harvests';
    protected string $singular = 'harvest';
    protected string $subtitle = 'Quantities harvested, quality and where the produce is stored.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['unit', 'quality', 'storage_location', 'planting.crop.name'];
    protected string $orderBy = 'harvest_date';

    protected function fields(): array
    {
        return [
            'planting_id' => $this->plantingField('Planting', true),
            'harvest_date' => ['label' => 'Harvest date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 4],
            'quantity' => ['label' => 'Quantity', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|numeric|min:0', 'col' => 4],
            'unit' => ['label' => 'Unit', 'type' => 'select', 'rules' => 'required',
                'options' => $this->enum(['kg', 'bags', 'sacks', 'tonnes', 'bunches', 'crates', 'pieces']), 'default' => 'kg', 'col' => 4],
            'quality' => ['label' => 'Quality', 'type' => 'select', 'rules' => 'nullable',
                'options' => $this->enum(['Grade A', 'Grade B', 'Grade C']), 'col' => 6],
            'storage_location' => ['label' => 'Stored at', 'rules' => 'nullable|string|max:255', 'col' => 6],
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Date', 'value' => fn ($m) => $m->harvest_date, 'type' => 'date'],
            ['label' => 'Planting', 'value' => fn ($m) => $m->planting->label],
            ['label' => 'Quantity', 'value' => fn ($m) => rtrim(rtrim(number_format($m->quantity, 2), '0'), '.').' '.$m->unit, 'type' => 'number'],
            ['label' => 'Quality', 'value' => fn ($m) => $m->quality ?: '—', 'type' => 'badge'],
            ['label' => 'Stored at', 'value' => fn ($m) => $m->storage_location ?: '—'],
        ];
    }
}

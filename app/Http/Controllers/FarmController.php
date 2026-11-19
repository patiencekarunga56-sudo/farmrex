<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Builder;

class FarmController extends CrudController
{
    protected string $model = Farm::class;
    protected string $route = 'farms';
    protected string $title = 'Fields & Plots';
    protected string $singular = 'field';
    protected string $subtitle = 'The gardens, plots and fields where the group grows crops.';
    protected array $search = ['name', 'location', 'soil_type'];

    protected function baseQuery(): Builder
    {
        return Farm::query()->withCount('plantings');
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Field / plot name', 'rules' => 'required|string|max:255', 'col' => 6],
            'location' => ['label' => 'Location', 'rules' => 'nullable|string|max:255', 'col' => 6],
            'size_acres' => ['label' => 'Size (acres)', 'type' => 'number', 'step' => '0.01', 'rules' => 'nullable|numeric|min:0', 'col' => 6],
            'soil_type' => ['label' => 'Soil type', 'type' => 'select', 'rules' => 'nullable|string',
                'options' => $this->enum(['Loam', 'Clay', 'Sandy', 'Sandy loam', 'Clay loam', 'Other']), 'col' => 6],
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Field', 'value' => fn ($m) => $m->name],
            ['label' => 'Location', 'value' => fn ($m) => $m->location ?: '—'],
            ['label' => 'Size (acres)', 'value' => fn ($m) => $m->size_acres, 'type' => 'number'],
            ['label' => 'Soil', 'value' => fn ($m) => $m->soil_type ?: '—'],
            ['label' => 'Plantings', 'value' => fn ($m) => $m->plantings_count, 'type' => 'number'],
        ];
    }
}

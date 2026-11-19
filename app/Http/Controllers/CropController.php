<?php

namespace App\Http\Controllers;

use App\Models\Crop;

class CropController extends CrudController
{
    protected string $model = Crop::class;
    protected string $route = 'crops';
    protected string $title = 'Crops';
    protected string $singular = 'crop';
    protected string $subtitle = 'Crop varieties grown by the group.';
    protected array $search = ['name', 'variety', 'category'];

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Crop name', 'rules' => 'required|string|max:255', 'col' => 6],
            'variety' => ['label' => 'Variety', 'rules' => 'nullable|string|max:255', 'col' => 6],
            'category' => ['label' => 'Category', 'type' => 'select', 'rules' => 'nullable|string',
                'options' => $this->enum(['Cereal', 'Legume', 'Vegetable', 'Fruit', 'Root & tuber', 'Cash crop', 'Other']), 'col' => 6],
            'days_to_maturity' => ['label' => 'Days to maturity', 'type' => 'number', 'step' => '1',
                'rules' => 'nullable|integer|min:1|max:1500', 'col' => 6,
                'hint' => 'Used to suggest the expected harvest date when you plant it.'],
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Crop', 'value' => fn ($m) => $m->name],
            ['label' => 'Variety', 'value' => fn ($m) => $m->variety ?: '—'],
            ['label' => 'Category', 'value' => fn ($m) => $m->category ?: '—'],
            ['label' => 'Days to maturity', 'value' => fn ($m) => $m->days_to_maturity ?: '—', 'type' => 'number'],
        ];
    }
}

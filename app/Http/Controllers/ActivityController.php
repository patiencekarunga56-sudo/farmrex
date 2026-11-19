<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Support\Str;

class ActivityController extends CrudController
{
    protected string $model = Activity::class;
    protected string $route = 'activities';
    protected string $title = 'Farm Activities';
    protected string $singular = 'activity';
    protected string $subtitle = 'Land preparation, weeding, spraying, fertiliser application and other field work.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['activity_type', 'description', 'performed_by', 'planting.crop.name'];
    protected string $orderBy = 'activity_date';

    protected function fields(): array
    {
        return [
            'planting_id' => $this->plantingField('Crop planting', true),
            'activity_type' => ['label' => 'Activity', 'type' => 'select', 'rules' => 'required',
                'options' => $this->enum(['Land preparation', 'Planting', 'Weeding', 'Fertiliser application', 'Spraying', 'Irrigation', 'Pruning / thinning', 'Mulching', 'Harvesting', 'Other']), 'col' => 6],
            'activity_date' => ['label' => 'Date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 6],
            'performed_by' => ['label' => 'Done by', 'rules' => 'nullable|string|max:255', 'col' => 12],
            'description' => ['label' => 'Details', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Date', 'value' => fn ($m) => $m->activity_date, 'type' => 'date'],
            ['label' => 'Planting', 'value' => fn ($m) => $m->planting->label],
            ['label' => 'Activity', 'value' => fn ($m) => $m->activity_type, 'type' => 'badge'],
            ['label' => 'Details', 'value' => fn ($m) => Str::limit((string) $m->description, 50) ?: '—'],
            ['label' => 'Done by', 'value' => fn ($m) => $m->performed_by ?: '—'],
        ];
    }
}

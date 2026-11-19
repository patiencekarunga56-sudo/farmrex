<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\Planting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ObservationController extends CrudController
{
    protected string $model = Observation::class;
    protected string $route = 'observations';
    protected string $title = 'Crop Observations';
    protected string $singular = 'observation';
    protected string $subtitle = 'Growth-stage updates, pest and disease sightings and other field notes.';
    protected string $hint = 'Choosing a growth stage here also updates the stage shown on the planting.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['category', 'description', 'action_taken', 'planting.crop.name'];
    protected string $orderBy = 'observed_on';

    protected function fields(): array
    {
        return [
            'planting_id' => $this->plantingField('Planting', true),
            'observed_on' => ['label' => 'Date observed', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 4],
            'category' => ['label' => 'Type', 'type' => 'select', 'rules' => 'required',
                'options' => $this->enum(['Growth stage update', 'Pest', 'Disease', 'Weather damage', 'Other']), 'col' => 4],
            'severity' => ['label' => 'Severity', 'type' => 'select', 'rules' => 'nullable',
                'options' => $this->enum(['Low', 'Medium', 'High']), 'col' => 4],
            'growth_stage' => ['label' => 'New growth stage (optional)', 'type' => 'select', 'rules' => 'nullable',
                'options' => $this->enum(Planting::STAGES), 'col' => 12],
            'description' => ['label' => 'What did you see?', 'type' => 'textarea', 'rules' => 'required|string|max:2000'],
            'action_taken' => ['label' => 'Action taken', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function afterSave(Model $item, bool $created): void
    {
        if ($item->growth_stage) {
            $planting = $item->planting;
            $planting->growth_stage = $item->growth_stage;
            if ($item->growth_stage === 'Harvested') {
                $planting->status = 'Completed';
            }
            $planting->save();
        }
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Date', 'value' => fn ($m) => $m->observed_on, 'type' => 'date'],
            ['label' => 'Planting', 'value' => fn ($m) => $m->planting->label],
            ['label' => 'Type', 'value' => fn ($m) => $m->category, 'type' => 'badge'],
            ['label' => 'Severity', 'value' => fn ($m) => $m->severity ?: '—', 'type' => 'badge'],
            ['label' => 'Notes', 'value' => fn ($m) => Str::limit((string) $m->description, 50)],
        ];
    }
}

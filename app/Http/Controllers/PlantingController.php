<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\Farm;
use App\Models\Planting;
use App\Models\Reminder;
use Illuminate\Database\Eloquent\Model;

class PlantingController extends CrudController
{
    protected string $model = Planting::class;
    protected string $route = 'plantings';
    protected string $title = 'Plantings';
    protected string $singular = 'planting';
    protected string $subtitle = 'Crop records and planting schedules.';
    protected array $with = ['crop', 'farm'];
    protected array $search = ['crop.name', 'farm.name', 'growth_stage', 'notes'];
    protected string $orderBy = 'planting_date';

    protected function fields(): array
    {
        return [
            'crop_id' => ['label' => 'Crop', 'type' => 'select', 'rules' => 'required',
                'options' => Crop::orderBy('name')->pluck('name', 'id')->all(), 'col' => 6],
            'farm_id' => ['label' => 'Field / plot', 'type' => 'select', 'rules' => 'required',
                'options' => Farm::orderBy('name')->pluck('name', 'id')->all(), 'col' => 6],
            'planting_date' => ['label' => 'Planting date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 4],
            'expected_harvest_date' => ['label' => 'Expected harvest date', 'type' => 'date',
                'rules' => 'nullable|date|after_or_equal:planting_date', 'col' => 4,
                'hint' => 'Leave blank to calculate it from the crop\'s days to maturity.'],
            'area_acres' => ['label' => 'Area planted (acres)', 'type' => 'number', 'step' => '0.01', 'rules' => 'nullable|numeric|min:0', 'col' => 4],
            'growth_stage' => ['label' => 'Growth stage', 'type' => 'select', 'rules' => 'required',
                'options' => $this->enum(Planting::STAGES), 'default' => 'Planted', 'col' => 6],
            'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required',
                'options' => $this->enum(['Active', 'Completed']), 'default' => 'Active', 'col' => 6],
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function prepare(array $data, ?Model $item): array
    {
        if (empty($data['expected_harvest_date'])) {
            $days = Crop::find($data['crop_id'])?->days_to_maturity;
            $data['expected_harvest_date'] = $days
                ? \Carbon\Carbon::parse($data['planting_date'])->addDays($days)->toDateString()
                : null;
        }

        if ($data['growth_stage'] === 'Harvested') {
            $data['status'] = 'Completed';
        }

        return $data;
    }

    protected function afterSave(Model $item, bool $created): void
    {
        // Automatically add a "harvest is due" reminder for new plantings.
        if ($created && $item->expected_harvest_date) {
            $item->load('crop', 'farm');
            Reminder::create([
                'title' => 'Harvest due: '.$item->crop->name.' ('.$item->farm->name.')',
                'reminder_type' => 'Harvest',
                'due_date' => $item->expected_harvest_date,
                'status' => 'Pending',
                'planting_id' => $item->id,
                'user_id' => auth()->id(),
            ]);
        }
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Crop', 'value' => fn ($m) => $m->crop->name],
            ['label' => 'Field', 'value' => fn ($m) => $m->farm->name],
            ['label' => 'Planted', 'value' => fn ($m) => $m->planting_date, 'type' => 'date'],
            ['label' => 'Expected harvest', 'value' => fn ($m) => $m->expected_harvest_date, 'type' => 'date'],
            ['label' => 'Stage', 'value' => fn ($m) => $m->growth_stage, 'type' => 'badge'],
            ['label' => 'Status', 'value' => fn ($m) => $m->status, 'type' => 'badge'],
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\LabourRecord;
use Illuminate\Database\Eloquent\Model;

class LabourRecordController extends CrudController
{
    protected string $model = LabourRecord::class;
    protected string $route = 'labour';
    protected string $title = 'Labour';
    protected string $singular = 'labour record';
    protected string $subtitle = 'Workers, tasks, days worked and what was paid.';
    protected string $hint = 'The amount paid is worked out for you and counted automatically under Expenses.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['worker_name', 'task'];
    protected string $orderBy = 'work_date';

    protected function fields(): array
    {
        return [
            'worker_name' => ['label' => 'Worker', 'rules' => 'required|string|max:255', 'col' => 6],
            'task' => ['label' => 'Task', 'rules' => 'required|string|max:255', 'col' => 6, 'placeholder' => 'e.g. Weeding'],
            'work_date' => ['label' => 'Date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString(), 'col' => 4],
            'days_worked' => ['label' => 'Days worked', 'type' => 'number', 'step' => '0.5', 'rules' => 'required|numeric|min:0.5', 'default' => 1, 'col' => 4],
            'rate_per_day' => ['label' => 'Rate per day (UGX)', 'type' => 'money', 'rules' => 'required|numeric|min:0', 'col' => 4],
            'planting_id' => $this->plantingField('Planting (optional)'),
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    protected function prepare(array $data, ?Model $item): array
    {
        $data['amount'] = round(((float) $data['days_worked']) * ((float) $data['rate_per_day']), 2);

        return $data;
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Date', 'value' => fn ($m) => $m->work_date, 'type' => 'date'],
            ['label' => 'Worker', 'value' => fn ($m) => $m->worker_name],
            ['label' => 'Task', 'value' => fn ($m) => $m->task],
            ['label' => 'Days', 'value' => fn ($m) => rtrim(rtrim(number_format($m->days_worked, 2), '0'), '.'), 'type' => 'number'],
            ['label' => 'Amount', 'value' => fn ($m) => $m->amount, 'type' => 'money'],
        ];
    }
}

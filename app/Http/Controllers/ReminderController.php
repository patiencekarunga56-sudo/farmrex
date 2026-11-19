<?php

namespace App\Http\Controllers;

use App\Models\Reminder;

class ReminderController extends CrudController
{
    protected string $model = Reminder::class;
    protected string $route = 'reminders';
    protected string $title = 'Reminders';
    protected string $singular = 'reminder';
    protected string $subtitle = 'Date-based reminders for upcoming farm work.';
    protected array $with = ['planting.crop', 'planting.farm'];
    protected array $search = ['title', 'reminder_type', 'notes'];
    protected string $orderBy = 'due_date';
    protected string $orderDir = 'asc';

    protected function fields(): array
    {
        return [
            'title' => ['label' => 'Reminder', 'rules' => 'required|string|max:255', 'col' => 12, 'placeholder' => 'e.g. Second weeding – Field A'],
            'reminder_type' => ['label' => 'Type', 'type' => 'select', 'rules' => 'nullable',
                'options' => $this->enum(['Planting', 'Weeding', 'Fertiliser', 'Spraying', 'Irrigation', 'Harvest', 'Meeting', 'Other']), 'col' => 4],
            'due_date' => ['label' => 'Due date', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->addDays(7)->toDateString(), 'col' => 4],
            'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required', 'options' => $this->enum(['Pending', 'Done']), 'default' => 'Pending', 'col' => 4],
            'planting_id' => $this->plantingField('Planting (optional)'),
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
        ];
    }

    public function complete($id)
    {
        $reminder = Reminder::findOrFail($id);
        abort_unless($this->canModify($reminder), 403);
        $reminder->update(['status' => 'Done']);

        return back()->with('success', 'Reminder marked as done.');
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Due', 'value' => fn ($m) => $m->due_date, 'type' => 'date'],
            ['label' => 'Reminder', 'value' => fn ($m) => $m->title],
            ['label' => 'Type', 'value' => fn ($m) => $m->reminder_type ?: '—'],
            ['label' => 'Planting', 'value' => fn ($m) => $m->planting?->label ?: '—'],
            ['label' => 'Status', 'value' => fn ($m) => ($m->status === 'Pending' && $m->due_date->isPast() && ! $m->due_date->isToday()) ? 'Overdue' : $m->status, 'type' => 'badge'],
        ];
    }
}

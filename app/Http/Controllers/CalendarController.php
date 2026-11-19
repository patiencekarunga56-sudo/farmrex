<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Harvest;
use App\Models\Planting;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        try {
            $month = Carbon::createFromFormat('Y-m-d', ($request->query('month') ?: now()->format('Y-m')).'-01')->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
        }

        $start = $month->copy()->startOfWeek();
        $end = $month->copy()->endOfMonth()->endOfWeek();
        $events = [];
        $add = function ($date, $type, $label, $url) use (&$events) {
            $events[$date->format('Y-m-d')][] = ['type' => $type, 'label' => $label, 'url' => $url];
        };

        Reminder::with('planting.crop')->whereBetween('due_date', [$start, $end])->get()
            ->each(fn ($r) => $add($r->due_date, $r->status === 'Done' ? 'done' : 'reminder', $r->title, route('reminders.show', $r->id)));

        Activity::with('planting.crop')->whereBetween('activity_date', [$start, $end])->get()
            ->each(fn ($a) => $add($a->activity_date, 'activity', $a->activity_type.' – '.($a->planting->crop->name ?? ''), route('activities.show', $a->id)));

        Planting::with('crop', 'farm')->whereBetween('planting_date', [$start, $end])->get()
            ->each(fn ($p) => $add($p->planting_date, 'planting', 'Planted '.$p->crop->name, route('plantings.show', $p->id)));

        Planting::with('crop', 'farm')->whereBetween('expected_harvest_date', [$start, $end])->get()
            ->each(fn ($p) => $add($p->expected_harvest_date, 'harvest-due', 'Harvest due: '.$p->crop->name, route('plantings.show', $p->id)));

        Harvest::with('planting.crop')->whereBetween('harvest_date', [$start, $end])->get()
            ->each(fn ($h) => $add($h->harvest_date, 'harvest', 'Harvested '.($h->planting->crop->name ?? ''), route('harvests.show', $h->id)));

        $weeks = [];
        $i = 0;
        for ($d = $start->copy(); $d <= $end; $d->addDay(), $i++) {
            $weeks[intdiv($i, 7)][] = [
                'date' => $d->copy(),
                'inMonth' => $d->month === $month->month,
                'isToday' => $d->isToday(),
                'events' => $events[$d->format('Y-m-d')] ?? [],
            ];
        }

        return view('calendar.index', [
            'month' => $month,
            'weeks' => $weeks,
            'prev' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
        ]);
    }
}

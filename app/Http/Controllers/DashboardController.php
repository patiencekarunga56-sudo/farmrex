<?php

namespace App\Http\Controllers;
use App\Models\Harvest;
use App\Models\Observation;
use App\Models\Planting;
use App\Models\Reminder;
use App\Services\Ledger;
use App\Services\Weather;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request,Weather $weather)
    {
        $period = (string) $request->query('period');
        $period = array_key_exists($period, Ledger::PERIODS) ? $period : 'month';
        [$from, $to] = Ledger::range($period);

        $income = Ledger::incomeByCategory($from, $to);
        $expense = Ledger::expenseByCategory($from, $to);
        $totalIncome = $income->sum();
        $totalExpense = $expense->sum();

        $activePlantings = Planting::with(['crop', 'farm'])
            ->where('status', 'Active')->orderBy('expected_harvest_date')->get();

        $reminders = Reminder::with('planting.crop')
            ->where('status', 'Pending')->orderBy('due_date')->limit(5)->get();

        $monthly = Ledger::monthly(6);
        $chartMax = max(1, collect($monthly)->flatMap(fn ($m) => [$m['income'], $m['expense']])->max());

        return view('dashboard', [
            'period' => $period,
            'periods' => Ledger::PERIODS,
            'income' => $income,
            'expense' => $expense,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'net' => $totalIncome - $totalExpense,
            'activePlantings' => $activePlantings,
            'reminders' => $reminders,
            'overdue' => Reminder::where('status', 'Pending')->whereDate('due_date', '<', now())->count(),
            'recent' => Ledger::entries(null, null, 6),
           'weather' => Weather::forecast(),
            'monthly' => $monthly,
            'chartMax' => $chartMax,
            'recentObservations' => Observation::with('planting.crop')->latest('observed_on')->latest('id')->limit(4)->get(),
            'harvestCount' => Harvest::when($from, fn ($q) => $q->whereDate('harvest_date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('harvest_date', '<=', $to))->count(),
        ]);
    }
}

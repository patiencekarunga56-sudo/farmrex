<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\FarmInput;
use App\Models\Harvest;
use App\Models\Income;
use App\Models\LabourRecord;
use App\Models\Observation;
use App\Models\Planting;
use App\Services\Ledger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function dates(Request $request): array
    {
        $parse = function ($v, $default) {
            try {
                return $v ? Carbon::parse($v) : $default;
            } catch (\Throwable $e) {
                return $default;
            }
        };

        $from = $parse($request->query('from'), now()->startOfYear())->startOfDay();
        $to = $parse($request->query('to'), now())->endOfDay();

        return $to < $from ? [$to->copy()->startOfDay(), $from->copy()->endOfDay()] : [$from, $to];
    }

    private function sumBy($model, string $dateCol, string $sumCol, $from, $to)
    {
        return Ledger::scope($model::query(), $dateCol, $from, $to)
            ->whereNotNull('planting_id')
            ->selectRaw("planting_id, SUM({$sumCol}) as total")
            ->groupBy('planting_id')->pluck('total', 'planting_id');
    }

    public function index(Request $request)
    {
        [$from, $to] = $this->dates($request);

        $income = Ledger::incomeByCategory($from, $to);
        $expense = Ledger::expenseByCategory($from, $to);

        $inc = $this->sumBy(Income::class, 'income_date', 'amount', $from, $to);
        $costs = [
            $this->sumBy(Expense::class, 'expense_date', 'amount', $from, $to),
            $this->sumBy(FarmInput::class, 'purchase_date', 'total_cost', $from, $to),
            $this->sumBy(LabourRecord::class, 'work_date', 'amount', $from, $to),
        ];

        $harvests = Ledger::scope(Harvest::query(), 'harvest_date', $from, $to)
            ->selectRaw('planting_id, unit, SUM(quantity) as qty')->groupBy('planting_id', 'unit')->get()
            ->groupBy('planting_id');

        $plantings = Planting::with(['crop', 'farm'])->orderByDesc('planting_date')->get()->map(function ($p) use ($inc, $costs, $harvests) {
            $cost = collect($costs)->sum(fn ($c) => (float) ($c[$p->id] ?? 0));
            $revenue = (float) ($inc[$p->id] ?? 0);
            $harv = ($harvests[$p->id] ?? collect())->map(fn ($h) => rtrim(rtrim(number_format($h->qty, 2), '0'), '.').' '.$h->unit)->implode(', ');

            return ['planting' => $p, 'income' => $revenue, 'cost' => $cost, 'profit' => $revenue - $cost, 'harvest' => $harv ?: '—'];
        })->filter(fn ($r) => $r['income'] || $r['cost'] || $r['harvest'] !== '—')->values();

        $harvestByCrop = Ledger::scope(Harvest::query(), 'harvest_date', $from, $to)
            ->join('plantings', 'harvests.planting_id', '=', 'plantings.id')
            ->join('crops', 'plantings.crop_id', '=', 'crops.id')
            ->selectRaw('crops.name as crop, harvests.unit as unit, SUM(harvests.quantity) as qty')
            ->groupBy('crops.name', 'harvests.unit')->orderBy('crops.name')->get();

        $inputsByType = Ledger::scope(FarmInput::query(), 'purchase_date', $from, $to)
            ->selectRaw('type, COUNT(*) as items, SUM(total_cost) as total')->groupBy('type')->orderByDesc('total')->get();

        $observations = Ledger::scope(Observation::query(), 'observed_on', $from, $to)
            ->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category');

        $labour = Ledger::scope(LabourRecord::query(), 'work_date', $from, $to);

        return view('reports.index', [
            'from' => $from, 'to' => $to,
            'income' => $income, 'expense' => $expense,
            'totalIncome' => $income->sum(), 'totalExpense' => $expense->sum(),
            'plantings' => $plantings,
            'harvestByCrop' => $harvestByCrop,
            'inputsByType' => $inputsByType,
            'observations' => $observations,
            'labourDays' => (float) (clone $labour)->sum('days_worked'),
            'labourTotal' => (float) (clone $labour)->sum('amount'),
        ]);
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->dates($request);
        $rows = Ledger::entries($from, $to)->reverse()->values();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Type', 'Category', 'Description', 'Amount (UGX)']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['date']->format('Y-m-d'), ucfirst($r['kind']), $r['category'], $r['description'], $r['amount']]);
            }
            fclose($out);
        }, 'farmrex-ledger-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }
}

<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\FarmInput;
use App\Models\Income;
use App\Models\LabourRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Brings together everything that is money in / money out.
 * Income  = incomes table.
 * Expense = expenses table + farm input purchases + labour payments
 *           (inputs and labour are recorded in their own sections, so they
 *           are counted here automatically and never entered twice).
 */
class Ledger
{
    public const PERIODS = [
        'month' => 'This month',
        'last_month' => 'Last month',
        'year' => 'This year',
        'all' => 'All time',
    ];

    /** @return array{0:?Carbon,1:?Carbon} */
    public static function range(string $period): array
    {
        $now = now();

        return match ($period) {
            'all' => [null, null],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    public static function scope($query, string $column, $from, $to)
    {
        if ($from) {
            $query->whereDate($column, '>=', $from);
        }
        if ($to) {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }

    public static function incomeByCategory($from = null, $to = null): Collection
    {
        return self::scope(Income::query(), 'income_date', $from, $to)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->map(fn ($v) => (float) $v);
    }

    public static function expenseByCategory($from = null, $to = null): Collection
    {
        $rows = self::scope(Expense::query(), 'expense_date', $from, $to)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn ($v) => (float) $v);

        $inputs = (float) self::scope(FarmInput::query(), 'purchase_date', $from, $to)->sum('total_cost');
        $labour = (float) self::scope(LabourRecord::query(), 'work_date', $from, $to)->sum('amount');

        if ($inputs > 0) {
            $rows->put('Farm Inputs', $inputs);
        }
        if ($labour > 0) {
            $rows->put('Labour costs', $labour);
        }

        return $rows->sortDesc();
    }

    /** Every money entry, newest first. */
    public static function entries($from = null, $to = null, ?int $limit = null): Collection
    {
        $rows = collect();
        $fetch = fn ($q, $col) => self::scope($q, $col, $from, $to)
            ->orderByDesc($col)->orderByDesc('id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        foreach ($fetch(Income::query(), 'income_date') as $i) {
            $rows->push([
                'date' => $i->income_date, 'kind' => 'income', 'category' => $i->category,
                'description' => $i->description ?: ($i->produce ?: $i->category),
                'amount' => (float) $i->amount, 'url' => route('incomes.show', $i->id),
            ]);
        }
        foreach ($fetch(Expense::query(), 'expense_date') as $e) {
            $rows->push([
                'date' => $e->expense_date, 'kind' => 'expense', 'category' => $e->category,
                'description' => $e->description ?: ($e->paid_to ?: $e->category),
                'amount' => (float) $e->amount, 'url' => route('expenses.show', $e->id),
            ]);
        }
        foreach ($fetch(FarmInput::query(), 'purchase_date') as $f) {
            $rows->push([
                'date' => $f->purchase_date, 'kind' => 'expense', 'category' => 'Farm Inputs',
                'description' => $f->name.' ('.rtrim(rtrim(number_format($f->quantity, 2), '0'), '.').' '.$f->unit.')',
                'amount' => (float) $f->total_cost, 'url' => route('inputs.show', $f->id),
            ]);
        }
        foreach ($fetch(LabourRecord::query(), 'work_date') as $l) {
            $rows->push([
                'date' => $l->work_date, 'kind' => 'expense', 'category' => 'Labour costs',
                'description' => $l->worker_name.' – '.$l->task,
                'amount' => (float) $l->amount, 'url' => route('labour.show', $l->id),
            ]);
        }

        $rows = $rows->sortByDesc(fn ($r) => $r['date']->timestamp)->values();

        return $limit ? $rows->take($limit)->values() : $rows;
    }

    /** Income vs expense for the last N months (oldest first). */
    public static function monthly(int $months = 6): array
    {
        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonthsNoOverflow($i);
            $end = $start->copy()->endOfMonth();
            $out[] = [
                'label' => $start->format('M'),
                'income' => (float) self::scope(Income::query(), 'income_date', $start, $end)->sum('amount'),
                'expense' => (float) self::expenseByCategory($start, $end)->sum(),
            ];
        }

        return $out;
    }
}

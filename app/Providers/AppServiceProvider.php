<?php

namespace App\Providers;

use App\Models\Reminder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.farmrex');

        // Number of reminders that are overdue or due within 3 days (shown on the bell).
        View::composer('layouts.app', function ($view) {
            $count = 0;
            if (auth()->check() && Schema::hasTable('reminders')) {
                $count = Reminder::where('status', 'Pending')
                    ->whereDate('due_date', '<=', now()->addDays(3))
                    ->count();
            }
            $view->with('dueCount', $count);
        });
    }
}

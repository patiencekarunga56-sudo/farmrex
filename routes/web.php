<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FarmInputController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\LabourRecordController;
use App\Http\Controllers\ObservationController;
use App\Http\Controllers\PlantingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Production
    Route::resource('plantings', PlantingController::class);
    Route::resource('activities', ActivityController::class);
    Route::resource('observations', ObservationController::class);
    Route::resource('harvests', HarvestController::class);

    // Resources
    Route::resource('inputs', FarmInputController::class);
    Route::resource('labour', LabourRecordController::class)->parameters(['labour' => 'labour']);
    Route::resource('farms', FarmController::class);
    Route::resource('crops', CropController::class);

    // Finance
    Route::resource('incomes', IncomeController::class);
    Route::resource('expenses', ExpenseController::class);

    // Planning
    Route::resource('reminders', ReminderController::class);
    Route::patch('reminders/{reminder}/complete', [ReminderController::class, 'complete'])->name('reminders.complete');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');


    // Account
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    // Administration (administrator only): farmer accounts, access control and reports
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});

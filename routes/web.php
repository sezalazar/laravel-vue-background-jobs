<?php

use App\Http\Controllers\BackgroundJobController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// TODO: Add security to routes
Route::get('/background-jobs', [BackgroundJobController::class, 'index']);
Route::post('/background-jobs/{background_job}/cancelBackgroundJob', [BackgroundJobController::class, 'cancelBackgroundJob']);
Route::get('/background-jobs/{job}/logs', [BackgroundJobController::class, 'getLogs']);
Route::post('/background-jobs/runBackgroundJob', [BackgroundJobController::class, 'runBackgroundJob']);
Route::get('/background-jobs/allowed-classes', [BackgroundJobController::class, 'allowedClasses']);
Route::post('/background-jobs/class-methods', [BackgroundJobController::class, 'getClassMethods']);



require __DIR__.'/auth.php';

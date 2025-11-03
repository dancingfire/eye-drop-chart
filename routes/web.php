<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (login if not authenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard - protected by auth
Route::get('/dashboard', [ChartController::class, 'form'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Chart routes - require authentication
Route::middleware('auth')->group(function () {
    Route::post('/generate', [ChartController::class, 'generate'])->name('chart.generate');
    Route::post('/htmlchart', [ChartController::class, 'htmlchart'])->name('chart.htmlchart');
    
    // Template routes (user-specific)
    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('templates.index');
        Route::post('/', [TemplateController::class, 'store'])->name('templates.store');
        Route::get('/{id}', [TemplateController::class, 'show'])->name('templates.show');
        Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('templates.destroy');
    });
    
    // Medication management (all authenticated users)
    Route::prefix('medications')->name('medications.')->group(function () {
        Route::get('/', [MedicationController::class, 'index'])->name('index');
        Route::get('/create', [MedicationController::class, 'create'])->name('create');
        Route::post('/', [MedicationController::class, 'store'])->name('store');
        Route::get('/{medication}/edit', [MedicationController::class, 'edit'])->name('edit');
        Route::put('/{medication}', [MedicationController::class, 'update'])->name('update');
        Route::delete('/{medication}', [MedicationController::class, 'destroy'])->name('destroy');
    });
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes - require superuser (only user management now)
Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\EnsureSuperuser::class])->group(function () {
    // User management (superusers only)
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__.'/auth.php';

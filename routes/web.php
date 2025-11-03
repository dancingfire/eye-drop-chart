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
    
    // Template routes
    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('templates.index');
        Route::post('/', [TemplateController::class, 'store'])->name('templates.store');
        Route::get('/{id}', [TemplateController::class, 'show'])->name('templates.show');
        Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('templates.destroy');
    });
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes - require superuser
Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\EnsureSuperuser::class])->group(function () {
    // Medication management
    Route::get('medications', [MedicationController::class, 'index'])->name('medications.index');
    Route::get('medications/create', [MedicationController::class, 'create'])->name('medications.create');
    Route::post('medications', [MedicationController::class, 'store'])->name('medications.store');
    Route::get('medications/{medication}/edit', [MedicationController::class, 'edit'])->name('medications.edit');
    Route::put('medications/{medication}', [MedicationController::class, 'update'])->name('medications.update');
    Route::delete('medications/{medication}', [MedicationController::class, 'destroy'])->name('medications.destroy');
    
    // User management
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__.'/auth.php';

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChartController;

Route::get('/', [ChartController::class, 'form'])->name('chart.form');
Route::post('/generate', [ChartController::class, 'generate'])->name('chart.generate');
Route::post('/htmlchart', [ChartController::class, 'htmlchart'])->name('chart.htmlchart');

use App\Http\Controllers\MedicationController;

Route::prefix('admin')->group(function () {
    Route::get('medications', [MedicationController::class, 'index'])->name('medications.index');
    Route::get('medications/create', [MedicationController::class, 'create'])->name('medications.create');
    Route::post('medications', [MedicationController::class, 'store'])->name('medications.store');
    Route::get('medications/{medication}/edit', [MedicationController::class, 'edit'])->name('medications.edit');
    Route::put('medications/{medication}', [MedicationController::class, 'update'])->name('medications.update');
    Route::delete('medications/{medication}', [MedicationController::class, 'destroy'])->name('medications.destroy');
});

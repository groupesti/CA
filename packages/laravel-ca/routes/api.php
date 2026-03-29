<?php

declare(strict_types=1);

use CA\Http\Controllers\CaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CaController::class, 'index'])->name('ca.index');
Route::post('/', [CaController::class, 'store'])->name('ca.store');
Route::get('/{id}', [CaController::class, 'show'])->name('ca.show');
Route::put('/{id}', [CaController::class, 'update'])->name('ca.update');
Route::delete('/{id}', [CaController::class, 'destroy'])->name('ca.destroy');
Route::get('/{id}/hierarchy', [CaController::class, 'hierarchy'])->name('ca.hierarchy');

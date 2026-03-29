<?php

declare(strict_types=1);

use CA\Key\Http\Controllers\KeyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KeyController::class, 'index'])->name('ca.keys.index');
Route::post('/', [KeyController::class, 'store'])->name('ca.keys.store');
Route::get('/{uuid}', [KeyController::class, 'show'])->name('ca.keys.show');
Route::delete('/{uuid}', [KeyController::class, 'destroy'])->name('ca.keys.destroy');
Route::post('/{uuid}/export', [KeyController::class, 'export'])->name('ca.keys.export');
Route::post('/{uuid}/rotate', [KeyController::class, 'rotate'])->name('ca.keys.rotate');

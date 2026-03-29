<?php

declare(strict_types=1);

use CA\Csr\Http\Controllers\CsrController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CsrController::class, 'index'])->name('ca.csrs.index');
Route::post('/', [CsrController::class, 'store'])->name('ca.csrs.store');
Route::post('/import', [CsrController::class, 'import'])->name('ca.csrs.import');
Route::get('/{uuid}', [CsrController::class, 'show'])->name('ca.csrs.show');
Route::post('/{uuid}/approve', [CsrController::class, 'approve'])->name('ca.csrs.approve');
Route::post('/{uuid}/reject', [CsrController::class, 'reject'])->name('ca.csrs.reject');

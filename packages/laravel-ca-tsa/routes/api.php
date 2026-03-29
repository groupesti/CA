<?php

declare(strict_types=1);

use CA\Tsa\Http\Controllers\TsaController;
use CA\Tsa\Http\Middleware\TsaContentType;
use Illuminate\Support\Facades\Route;

Route::post('/', [TsaController::class, 'timestamp'])
    ->middleware(TsaContentType::class)
    ->name('tsa.timestamp');

Route::get('/info', [TsaController::class, 'info'])
    ->name('tsa.info');

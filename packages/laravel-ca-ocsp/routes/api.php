<?php

declare(strict_types=1);

use CA\OCSP\Http\Controllers\OcspController;
use Illuminate\Support\Facades\Route;

Route::post('/{caUuid}', [OcspController::class, 'postRequest'])
    ->name('ocsp.post');

Route::get('/{caUuid}/{encodedRequest}', [OcspController::class, 'getRequest'])
    ->where('encodedRequest', '.*')
    ->name('ocsp.get');

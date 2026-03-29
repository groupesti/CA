<?php

declare(strict_types=1);

use CA\Scep\Http\Controllers\ScepController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SCEP Protocol Routes
|--------------------------------------------------------------------------
|
| Standard SCEP endpoint: /pkiclient.exe
| This is the conventional URL used by SCEP clients (iOS, macOS, Windows, etc.)
|
| GET  /{ca_uuid}/pkiclient.exe?operation=GetCACert
| GET  /{ca_uuid}/pkiclient.exe?operation=GetCACaps
| GET  /{ca_uuid}/pkiclient.exe?operation=GetNextCACert
| GET  /{ca_uuid}/pkiclient.exe?operation=PKIOperation&message=<base64>
| POST /{ca_uuid}/pkiclient.exe (PKIOperation - binary DER body)
|
*/

Route::get('/{caUuid}/pkiclient.exe', [ScepController::class, 'get'])
    ->name('ca.scep.get');

Route::post('/{caUuid}/pkiclient.exe', [ScepController::class, 'post'])
    ->name('ca.scep.post');

<?php

declare(strict_types=1);

use CA\Est\Http\Controllers\EstController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EST Protocol Routes (RFC 7030)
|--------------------------------------------------------------------------
|
| Routes are served under /.well-known/est/{label}/ per RFC 7030 Section 3.2.2.
| The {label} parameter identifies the Certificate Authority by UUID or alias.
|
*/

Route::prefix('{label}')->group(function (): void {
    // GET /cacerts - CA Certificates (no auth required)
    Route::get('cacerts', [EstController::class, 'getCaCerts'])
        ->name('est.cacerts');

    // GET /csrattrs - CSR Attributes (no auth required)
    Route::get('csrattrs', [EstController::class, 'getCsrAttrs'])
        ->name('est.csrattrs');

    // POST /simpleenroll - Simple Enrollment (auth required)
    Route::post('simpleenroll', [EstController::class, 'simpleEnroll'])
        ->name('est.simpleenroll');

    // POST /simplereenroll - Simple Re-enrollment (client cert auth)
    Route::post('simplereenroll', [EstController::class, 'simpleReenroll'])
        ->name('est.simplereenroll');

    // POST /serverkeygen - Server-Side Key Generation (auth required)
    Route::post('serverkeygen', [EstController::class, 'serverKeyGen'])
        ->name('est.serverkeygen');

    // POST /fullcmc - Full CMC (auth required)
    Route::post('fullcmc', [EstController::class, 'fullCmc'])
        ->name('est.fullcmc');
});

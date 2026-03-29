<?php

declare(strict_types=1);

use CA\Ui\Http\Controllers\AuditController;
use CA\Ui\Http\Controllers\AuthorityController;
use CA\Ui\Http\Controllers\CertificateController;
use CA\Ui\Http\Controllers\CrlController;
use CA\Ui\Http\Controllers\CsrController;
use CA\Ui\Http\Controllers\DashboardController;
use CA\Ui\Http\Controllers\KeyController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('ca-ui.route_prefix', 'ca-admin'))
    ->middleware(config('ca-ui.middleware', ['web', 'auth']))
    ->name('ca.')
    ->group(function (): void {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Certificate Authorities
        Route::get('/authorities', [AuthorityController::class, 'index'])->name('authorities.index');
        Route::get('/authorities/create', [AuthorityController::class, 'create'])->name('authorities.create');
        Route::post('/authorities', [AuthorityController::class, 'store'])->name('authorities.store');
        Route::get('/authorities/{uuid}', [AuthorityController::class, 'show'])->name('authorities.show');

        // Certificates
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{uuid}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::post('/certificates/{uuid}/revoke', [CertificateController::class, 'revoke'])->name('certificates.revoke');
        Route::get('/certificates/{uuid}/export', [CertificateController::class, 'export'])->name('certificates.export');

        // Keys
        Route::get('/keys', [KeyController::class, 'index'])->name('keys.index');
        Route::get('/keys/{uuid}', [KeyController::class, 'show'])->name('keys.show');

        // CSRs
        Route::get('/csrs', [CsrController::class, 'index'])->name('csrs.index');
        Route::get('/csrs/{uuid}', [CsrController::class, 'show'])->name('csrs.show');
        Route::post('/csrs/{uuid}/approve', [CsrController::class, 'approve'])->name('csrs.approve');
        Route::post('/csrs/{uuid}/reject', [CsrController::class, 'reject'])->name('csrs.reject');

        // CRLs
        Route::get('/crls', [CrlController::class, 'index'])->name('crls.index');
        Route::post('/crls/{ca_uuid}/generate', [CrlController::class, 'generate'])->name('crls.generate');

        // Audit Log
        Route::get('/audit-log', [AuditController::class, 'index'])->name('audit.index');
    });

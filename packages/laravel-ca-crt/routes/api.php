<?php

declare(strict_types=1);

use CA\Crt\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CertificateController::class, 'index'])->name('ca.certificates.index');
Route::post('/', [CertificateController::class, 'store'])->name('ca.certificates.store');
Route::get('/expiring', [CertificateController::class, 'expiring'])->name('ca.certificates.expiring');
Route::get('/{uuid}', [CertificateController::class, 'show'])->name('ca.certificates.show');
Route::post('/{uuid}/revoke', [CertificateController::class, 'revoke'])->name('ca.certificates.revoke');
Route::post('/{uuid}/renew', [CertificateController::class, 'renew'])->name('ca.certificates.renew');
Route::get('/{uuid}/chain', [CertificateController::class, 'chain'])->name('ca.certificates.chain');
Route::get('/{uuid}/export', [CertificateController::class, 'export'])->name('ca.certificates.export');
Route::get('/{uuid}/verify', [CertificateController::class, 'verify'])->name('ca.certificates.verify');

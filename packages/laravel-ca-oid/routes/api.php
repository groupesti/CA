<?php

declare(strict_types=1);

use CA\Oid\Http\Controllers\OidController;
use Illuminate\Support\Facades\Route;

$middleware = config('ca-oid.route_middleware', ['auth:api']);

Route::prefix('oids')->middleware($middleware)->group(function () {
    Route::get('/', [OidController::class, 'index'])->name('ca.oids.index');
    Route::get('/search', [OidController::class, 'search'])->name('ca.oids.search');
    Route::get('/categories', [OidController::class, 'categories'])->name('ca.oids.categories');
    Route::get('/{oid}', [OidController::class, 'show'])->name('ca.oids.show')->where('oid', '[\d.]+');
    Route::post('/', [OidController::class, 'store'])->name('ca.oids.store');
    Route::delete('/{oid}', [OidController::class, 'destroy'])->name('ca.oids.destroy')->where('oid', '[\d.]+');
});

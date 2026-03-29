<?php

declare(strict_types=1);

use CA\Acme\Http\Controllers\AcmeController;
use CA\Acme\Http\Middleware\AcmeContentType;
use CA\Acme\Http\Middleware\AcmeJwsMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ACME Protocol Routes (RFC 8555)
|--------------------------------------------------------------------------
|
| These routes implement the ACME protocol endpoints for automated
| certificate management. All POST endpoints use JWS authentication.
|
*/

Route::prefix(config('ca-acme.route_prefix', 'acme'))
    ->middleware(config('ca-acme.middleware', ['api']))
    ->group(function (): void {
        // Directory - no authentication required
        Route::get('/directory', [AcmeController::class, 'directory'])
            ->name('acme.directory');

        // New Nonce - no authentication required
        Route::match(['head', 'get'], '/new-nonce', [AcmeController::class, 'newNonce'])
            ->name('acme.new-nonce');

        // Authenticated ACME endpoints (JWS required)
        Route::middleware([AcmeContentType::class, AcmeJwsMiddleware::class])
            ->group(function (): void {
                Route::post('/new-account', [AcmeController::class, 'newAccount'])
                    ->name('acme.new-account');

                Route::post('/new-order', [AcmeController::class, 'newOrder'])
                    ->name('acme.new-order');

                Route::post('/order/{uuid}', [AcmeController::class, 'getOrder'])
                    ->name('acme.order')
                    ->whereUuid('uuid');

                Route::post('/order/{uuid}/finalize', [AcmeController::class, 'finalizeOrder'])
                    ->name('acme.order.finalize')
                    ->whereUuid('uuid');

                Route::post('/authorization/{uuid}', [AcmeController::class, 'getAuthorization'])
                    ->name('acme.authorization')
                    ->whereUuid('uuid');

                Route::post('/challenge/{uuid}', [AcmeController::class, 'getChallenge'])
                    ->name('acme.challenge')
                    ->whereUuid('uuid');

                Route::post('/certificate/{uuid}', [AcmeController::class, 'getCertificate'])
                    ->name('acme.certificate')
                    ->whereUuid('uuid');

                Route::post('/revoke-cert', [AcmeController::class, 'revokeCertificate'])
                    ->name('acme.revoke-cert');
            });
    });

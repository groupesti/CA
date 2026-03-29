<?php

declare(strict_types=1);

use CA\Policy\Http\Controllers\IssuanceRuleController;
use CA\Policy\Http\Controllers\NameConstraintController;
use CA\Policy\Http\Controllers\PolicyController;
use Illuminate\Support\Facades\Route;

Route::prefix('policies')->group(function (): void {
    Route::get('/', [PolicyController::class, 'index']);
    Route::post('/', [PolicyController::class, 'store']);
    Route::post('/evaluate', [PolicyController::class, 'evaluate']);
    Route::get('/{id}', [PolicyController::class, 'show']);
    Route::put('/{id}', [PolicyController::class, 'update']);
    Route::delete('/{id}', [PolicyController::class, 'destroy']);
});

Route::prefix('name-constraints')->group(function (): void {
    Route::get('/', [NameConstraintController::class, 'index']);
    Route::post('/', [NameConstraintController::class, 'store']);
    Route::get('/{id}', [NameConstraintController::class, 'show']);
    Route::put('/{id}', [NameConstraintController::class, 'update']);
    Route::delete('/{id}', [NameConstraintController::class, 'destroy']);
});

Route::prefix('issuance-rules')->group(function (): void {
    Route::get('/', [IssuanceRuleController::class, 'index']);
    Route::post('/', [IssuanceRuleController::class, 'store']);
    Route::post('/reorder', [IssuanceRuleController::class, 'reorder']);
    Route::get('/{id}', [IssuanceRuleController::class, 'show']);
    Route::put('/{id}', [IssuanceRuleController::class, 'update']);
    Route::delete('/{id}', [IssuanceRuleController::class, 'destroy']);
    Route::post('/{id}/enable', [IssuanceRuleController::class, 'enable']);
    Route::post('/{id}/disable', [IssuanceRuleController::class, 'disable']);
});

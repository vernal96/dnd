<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ActorInstanceController;
use App\Http\Controllers\Api\AuthSessionController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameSceneStateController;
use App\Http\Controllers\Api\SceneTemplateController;
use App\Http\Middleware\EnsureFrontendOrigin;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        EnsureFrontendOrigin::class,
    ])
    ->group(function (): void {
        Route::get('/session', [AuthSessionController::class, 'show']);
        Route::middleware([ValidateCsrfToken::class])->group(function (): void {
            Route::post('/login', [AuthSessionController::class, 'login']);
            Route::post('/register', [AuthSessionController::class, 'register']);
            Route::post('/logout', [AuthSessionController::class, 'destroy']);
        });
    });

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    EnsureFrontendOrigin::class,
    Authenticate::using('web'),
])->group(function (): void {
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{game}', [GameController::class, 'show']);

    Route::get('/scene-templates', [SceneTemplateController::class, 'index']);
    Route::get('/scene-templates/{sceneTemplate}', [SceneTemplateController::class, 'show']);

    Route::get('/scene-states', [GameSceneStateController::class, 'index']);
    Route::get('/scene-states/{gameSceneState}', [GameSceneStateController::class, 'show']);

    Route::get('/actors', [ActorInstanceController::class, 'index']);
    Route::get('/actors/{actorInstance}', [ActorInstanceController::class, 'show']);
});

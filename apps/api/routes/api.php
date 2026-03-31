<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ActorInstanceController;
use App\Http\Controllers\Api\AuthSessionController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameImageController;
use App\Http\Controllers\Api\GameInvitationController;
use App\Http\Controllers\Api\GameSceneStateController;
use App\Http\Controllers\Api\RaceController;
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
            Route::post('/forgot-password', [AuthSessionController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthSessionController::class, 'resetPassword']);
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
    Route::get('/races', [RaceController::class, 'index']);
    Route::get('/races/{race}', [RaceController::class, 'show'])->where('race', '[a-z0-9-]+');

    Route::get('/games', [GameController::class, 'index']);
    Route::post('/games', [GameController::class, 'store']);
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::patch('/games/{game}/status', [GameController::class, 'updateStatus']);
    Route::get('/games/{game}/images', [GameImageController::class, 'index']);
    Route::post('/games/{game}/images', [GameImageController::class, 'store']);
    Route::get('/games/{game}/images/{image}', [GameImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
    Route::post('/games/{game}/invitations', [GameController::class, 'inviteMember']);
    Route::delete('/games/{game}/members/{member}', [GameController::class, 'removeMember']);
    Route::get('/game-invitations', [GameInvitationController::class, 'index']);
    Route::post('/game-invitations/{token}/accept', [GameInvitationController::class, 'accept']);
    Route::post('/game-invitations/{token}/decline', [GameInvitationController::class, 'decline']);

    Route::get('/scene-templates', [SceneTemplateController::class, 'index']);
    Route::get('/scene-templates/{sceneTemplate}', [SceneTemplateController::class, 'show']);

    Route::get('/scene-states', [GameSceneStateController::class, 'index']);
    Route::get('/scene-states/{gameSceneState}', [GameSceneStateController::class, 'show']);

    Route::get('/actors', [ActorInstanceController::class, 'index']);
    Route::get('/actors/{actorInstance}', [ActorInstanceController::class, 'show']);
});

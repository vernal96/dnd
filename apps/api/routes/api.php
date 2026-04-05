<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ActorInstanceController;
use App\Http\Controllers\Api\ActorController;
use App\Http\Controllers\Api\ActorImageController;
use App\Http\Controllers\Api\AuthSessionController;
use App\Http\Controllers\Api\AbilityController;
use App\Http\Controllers\Api\CharacterClassController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameImageController;
use App\Http\Controllers\Api\GameInvitationController;
use App\Http\Controllers\Api\GmRuntimeSceneController;
use App\Http\Controllers\Api\GameSceneController;
use App\Http\Controllers\Api\GameSceneStateController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemCatalogImageController;
use App\Http\Controllers\Api\PlayerCharacterController;
use App\Http\Controllers\Api\PlayerCharacterImageController;
use App\Http\Controllers\Api\PlayerGameController;
use App\Http\Controllers\Api\PlayerRuntimeSceneController;
use App\Http\Controllers\Api\RaceController;
use App\Http\Controllers\Api\SceneCatalogController;
use App\Http\Controllers\Api\SceneObjectImageController;
use App\Http\Controllers\Api\SceneSurfaceImageController;
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
    Route::get('/character-classes', [CharacterClassController::class, 'index']);
    Route::get('/character-classes/{characterClass}', [CharacterClassController::class, 'show'])->where('characterClass', '[a-z0-9-]+');
    Route::get('/character-abilities', [AbilityController::class, 'index']);
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{item}', [ItemController::class, 'show'])->where('item', '[a-z0-9-]+');
    Route::get('/item-images/{image}', [ItemCatalogImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
    Route::get('/player/characters', [PlayerCharacterController::class, 'index']);
    Route::post('/player/characters', [PlayerCharacterController::class, 'store']);
    Route::patch('/player/characters/{character}/image', [PlayerCharacterController::class, 'updateImage']);
    Route::post('/player/character-images', [PlayerCharacterImageController::class, 'store']);
    Route::get('/player/character-images/{image}', [PlayerCharacterImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
    Route::get('/player/games/active', [PlayerGameController::class, 'indexActive']);
    Route::get('/player/games/{game}/runtime/scene', [PlayerRuntimeSceneController::class, 'showActive']);
    Route::post('/player/games/{game}/runtime/actors/{actor}/move', [PlayerRuntimeSceneController::class, 'moveActor']);
    Route::get('/gm/actors', [ActorController::class, 'index']);
    Route::post('/gm/actors', [ActorController::class, 'store']);
    Route::get('/gm/actors/{actor}', [ActorController::class, 'show']);
    Route::put('/gm/actors/{actor}', [ActorController::class, 'update']);
    Route::delete('/gm/actors/{actor}', [ActorController::class, 'destroy']);
    Route::get('/gm/actor-images', [ActorImageController::class, 'index']);
    Route::post('/gm/actor-images', [ActorImageController::class, 'store']);
    Route::get('/gm/actor-images/{image}', [ActorImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
    Route::get('/games', [GameController::class, 'index']);
    Route::post('/games', [GameController::class, 'store']);
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::patch('/games/{game}/status', [GameController::class, 'updateStatus']);
    Route::get('/games/{game}/images', [GameImageController::class, 'index']);
    Route::post('/games/{game}/images', [GameImageController::class, 'store']);
    Route::get('/games/{game}/images/{image}', [GameImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
    Route::post('/games/{game}/invitations', [GameController::class, 'inviteMember']);
    Route::delete('/games/{game}/members/{member}', [GameController::class, 'removeMember']);
    Route::post('/games/{game}/scenes', [GameSceneController::class, 'store']);
    Route::get('/games/{game}/scenes/{scene}', [GameSceneController::class, 'show']);
    Route::put('/games/{game}/scenes/{scene}', [GameSceneController::class, 'update']);
    Route::delete('/games/{game}/scenes/{scene}', [GameSceneController::class, 'destroy']);
    Route::get('/games/{game}/runtime/scene', [GmRuntimeSceneController::class, 'showActive']);
    Route::post('/games/{game}/runtime/scenes/{sceneState}/activate', [GmRuntimeSceneController::class, 'activate']);
    Route::post('/games/{game}/runtime/actors/{actor}/move', [GmRuntimeSceneController::class, 'moveActor']);
    Route::post('/games/{game}/runtime/actors/spawn', [GmRuntimeSceneController::class, 'spawnActor']);
    Route::post('/games/{game}/runtime/cells/paint', [GmRuntimeSceneController::class, 'paintCell']);
    Route::post('/games/{game}/runtime/items/drop', [GmRuntimeSceneController::class, 'dropItem']);
    Route::get('/game-invitations', [GameInvitationController::class, 'index']);
    Route::get('/game-invitations/{token}/characters', [GameInvitationController::class, 'availableCharacters']);
    Route::post('/game-invitations/{token}/accept', [GameInvitationController::class, 'accept']);
    Route::post('/game-invitations/{token}/decline', [GameInvitationController::class, 'decline']);

    Route::get('/scene-templates', [SceneTemplateController::class, 'index']);
    Route::get('/scene-templates/{sceneTemplate}', [SceneTemplateController::class, 'show']);
    Route::get('/scene-catalog/surfaces', [SceneCatalogController::class, 'surfaces']);
    Route::get('/scene-catalog/objects', [SceneCatalogController::class, 'objects']);
    Route::get('/scene-catalog/object-images/{image}', [SceneObjectImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
    Route::get('/scene-catalog/surface-images/{image}', [SceneSurfaceImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');

    Route::get('/scene-states', [GameSceneStateController::class, 'index']);
    Route::get('/scene-states/{gameSceneState}', [GameSceneStateController::class, 'show']);

    Route::get('/actors', [ActorInstanceController::class, 'index']);
    Route::get('/actors/{actorInstance}', [ActorInstanceController::class, 'show']);
});

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
    Route::prefix('actor-catalog')->group(function (): void {
        Route::get('/races', [RaceController::class, 'index']);
        Route::get('/races/{race}', [RaceController::class, 'show'])->where('race', '[a-z0-9-]+');
        Route::get('/classes', [CharacterClassController::class, 'index']);
        Route::get('/classes/{characterClass}', [CharacterClassController::class, 'show'])->where('characterClass', '[a-z0-9-]+');
        Route::get('/abilities', [AbilityController::class, 'index']);
    });

    Route::get('/races', [RaceController::class, 'index']);
    Route::get('/races/{race}', [RaceController::class, 'show'])->where('race', '[a-z0-9-]+');
    Route::get('/character-classes', [CharacterClassController::class, 'index']);
    Route::get('/character-classes/{characterClass}', [CharacterClassController::class, 'show'])->where('characterClass', '[a-z0-9-]+');
    Route::get('/character-abilities', [AbilityController::class, 'index']);

    Route::prefix('items')->group(function (): void {
        Route::get('/', [ItemController::class, 'index']);
        Route::get('/{item}', [ItemController::class, 'show'])->where('item', '[a-z0-9-]+');
    });
    Route::get('/item-images/{image}', [ItemCatalogImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');

    Route::prefix('player')->group(function (): void {
        Route::prefix('characters')->group(function (): void {
            Route::get('/', [PlayerCharacterController::class, 'index']);
            Route::post('/', [PlayerCharacterController::class, 'store']);
            Route::patch('/{character}/image', [PlayerCharacterController::class, 'updateImage']);
        });

        Route::prefix('character-images')->group(function (): void {
            Route::post('/', [PlayerCharacterImageController::class, 'store']);
            Route::get('/{image}', [PlayerCharacterImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
        });

        Route::prefix('games')->group(function (): void {
            Route::get('/active', [PlayerGameController::class, 'indexActive']);
            Route::prefix('{game}/runtime')->group(function (): void {
                Route::get('/scene', [PlayerRuntimeSceneController::class, 'showActive']);
                Route::prefix('actors/{actor}')->group(function (): void {
                    Route::post('/move', [PlayerRuntimeSceneController::class, 'moveActor']);
                    Route::post('/actions', [PlayerRuntimeSceneController::class, 'performAction']);
                    Route::post('/equipment', [PlayerRuntimeSceneController::class, 'equipActor']);
                    Route::post('/action', [PlayerRuntimeSceneController::class, 'useAction']);
                    Route::post('/bonus-action', [PlayerRuntimeSceneController::class, 'useBonusAction']);
                    Route::post('/end-turn', [PlayerRuntimeSceneController::class, 'nextTurn']);
                });
            });
        });
    });

    Route::prefix('gm')->group(function (): void {
        Route::prefix('actors')->group(function (): void {
            Route::get('/', [ActorController::class, 'index']);
            Route::post('/', [ActorController::class, 'store']);
            Route::get('/{actor}', [ActorController::class, 'show']);
            Route::put('/{actor}', [ActorController::class, 'update']);
            Route::delete('/{actor}', [ActorController::class, 'destroy']);
        });

        Route::prefix('actor-images')->group(function (): void {
            Route::get('/', [ActorImageController::class, 'index']);
            Route::post('/', [ActorImageController::class, 'store']);
            Route::get('/{image}', [ActorImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
        });
    });

    Route::prefix('games')->group(function (): void {
        Route::get('/', [GameController::class, 'index']);
        Route::post('/', [GameController::class, 'store']);
        Route::get('/{game}', [GameController::class, 'show']);
        Route::patch('/{game}/status', [GameController::class, 'updateStatus']);

        Route::prefix('{game}/images')->group(function (): void {
            Route::get('/', [GameImageController::class, 'index']);
            Route::post('/', [GameImageController::class, 'store']);
            Route::get('/{image}', [GameImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
        });

        Route::post('/{game}/invitations', [GameController::class, 'inviteMember']);
        Route::delete('/{game}/members/{member}', [GameController::class, 'removeMember']);

        Route::prefix('{game}/scenes')->group(function (): void {
            Route::post('/', [GameSceneController::class, 'store']);
            Route::get('/{scene}', [GameSceneController::class, 'show']);
            Route::put('/{scene}', [GameSceneController::class, 'update']);
            Route::delete('/{scene}', [GameSceneController::class, 'destroy']);
        });

        Route::prefix('{game}/runtime')->group(function (): void {
            Route::get('/scene', [GmRuntimeSceneController::class, 'showActive']);
            Route::post('/scenes/{sceneState}/activate', [GmRuntimeSceneController::class, 'activate']);
            Route::prefix('encounter')->group(function (): void {
                Route::post('/start', [GmRuntimeSceneController::class, 'startEncounter']);
                Route::post('/end', [GmRuntimeSceneController::class, 'endEncounter']);
                Route::post('/end-turn', [GmRuntimeSceneController::class, 'nextTurn']);
            });
            Route::prefix('actors')->group(function (): void {
                Route::post('/spawn', [GmRuntimeSceneController::class, 'spawnActor']);
                Route::prefix('{actor}')->group(function (): void {
                    Route::post('/move', [GmRuntimeSceneController::class, 'moveActor']);
                    Route::post('/actions', [GmRuntimeSceneController::class, 'performAction']);
                    Route::post('/equipment', [GmRuntimeSceneController::class, 'equipActor']);
                    Route::post('/action', [GmRuntimeSceneController::class, 'useAction']);
                    Route::post('/bonus-action', [GmRuntimeSceneController::class, 'useBonusAction']);
                });
            });
            Route::prefix('cells')->group(function (): void {
                Route::post('/paint', [GmRuntimeSceneController::class, 'paintCell']);
            });
            Route::prefix('items')->group(function (): void {
                Route::post('/drop', [GmRuntimeSceneController::class, 'dropItem']);
            });
        });
    });

    Route::prefix('game-invitations')->group(function (): void {
        Route::get('/', [GameInvitationController::class, 'index']);
        Route::get('/{token}/characters', [GameInvitationController::class, 'availableCharacters']);
        Route::post('/{token}/accept', [GameInvitationController::class, 'accept']);
        Route::post('/{token}/decline', [GameInvitationController::class, 'decline']);
    });

    Route::prefix('scene-templates')->group(function (): void {
        Route::get('/', [SceneTemplateController::class, 'index']);
        Route::get('/{sceneTemplate}', [SceneTemplateController::class, 'show']);
    });

    Route::prefix('scene-catalog')->group(function (): void {
        Route::get('/surfaces', [SceneCatalogController::class, 'surfaces']);
        Route::get('/objects', [SceneCatalogController::class, 'objects']);
        Route::get('/object-images/{image}', [SceneObjectImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
        Route::get('/surface-images/{image}', [SceneSurfaceImageController::class, 'show'])->where('image', '[A-Za-z0-9._-]+');
    });

    Route::prefix('scene-states')->group(function (): void {
        Route::get('/', [GameSceneStateController::class, 'index']);
        Route::get('/{gameSceneState}', [GameSceneStateController::class, 'show']);
    });

    Route::prefix('actors')->group(function (): void {
        Route::get('/', [ActorInstanceController::class, 'index']);
        Route::get('/{actorInstance}', [ActorInstanceController::class, 'show']);
    });
});

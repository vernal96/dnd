<?php

use App\Http\Controllers\Api\ActorInstanceController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameSceneStateController;
use App\Http\Controllers\Api\SceneTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);

Route::get('/scene-templates', [SceneTemplateController::class, 'index']);
Route::get('/scene-templates/{sceneTemplate}', [SceneTemplateController::class, 'show']);

Route::get('/scene-states', [GameSceneStateController::class, 'index']);
Route::get('/scene-states/{gameSceneState}', [GameSceneStateController::class, 'show']);

Route::get('/actors', [ActorInstanceController::class, 'index']);
Route::get('/actors/{actorInstance}', [ActorInstanceController::class, 'show']);

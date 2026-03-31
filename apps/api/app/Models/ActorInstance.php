<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Runtime actor placed inside a concrete game scene state.
 */
class ActorInstance extends Model
{
    use HasFactory;

    protected $table = 'actor_instances';

    protected $fillable = [
        'game_id',
        'game_scene_state_id',
        'player_character_id',
        'controlled_by_user_id',
        'kind',
        'controller_type',
        'name',
        'faction',
        'social_state',
        'status',
        'x',
        'y',
        'initiative',
        'hp_current',
        'hp_max',
        'is_hidden',
        'resources',
        'temporary_effects',
        'runtime_state',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'resources' => 'array',
        'temporary_effects' => 'array',
        'runtime_state' => 'array',
    ];

    /**
     * Возвращает игру, которой принадлежит этот runtime-актор.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    /**
     * Возвращает активное состояние сцены, в котором сейчас находится актор.
     */
    public function sceneState(): BelongsTo
    {
        return $this->belongsTo(GameSceneState::class, 'game_scene_state_id');
    }

    /**
     * Возвращает постоянного персонажа игрока, если актор создан на его основе.
     */
    public function playerCharacter(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class, 'player_character_id', 'id');
    }

    /**
     * Возвращает пользователя, который сейчас управляет актором.
     */
    public function controller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'controlled_by_user_id');
    }

    /**
     * Возвращает записи участия актора в encounter.
     */
    public function encounterParticipants(): HasMany
    {
        return $this->hasMany(EncounterParticipant::class, 'actor_id', 'id');
    }
}

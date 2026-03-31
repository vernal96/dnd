<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActorInstance extends Model
{
    use HasFactory;

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

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function sceneState(): BelongsTo
    {
        return $this->belongsTo(GameSceneState::class, 'game_scene_state_id');
    }

    public function playerCharacter(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class);
    }

    public function controller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'controlled_by_user_id');
    }

    public function encounterParticipants(): HasMany
    {
        return $this->hasMany(EncounterParticipant::class, 'actor_id');
    }
}

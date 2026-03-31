<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Encounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'game_scene_state_id',
        'status',
        'round',
        'current_participant_id',
        'trigger_type',
        'payload',
        'started_at',
        'resolved_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function sceneState(): BelongsTo
    {
        return $this->belongsTo(GameSceneState::class, 'game_scene_state_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EncounterParticipant::class);
    }

    public function currentParticipant(): BelongsTo
    {
        return $this->belongsTo(EncounterParticipant::class, 'current_participant_id');
    }
}

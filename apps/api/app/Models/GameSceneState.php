<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSceneState extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'scene_template_id',
        'status',
        'version',
        'grid_state',
        'objects_state',
        'visibility_state',
        'effects_state',
        'runtime_state',
        'loaded_at',
        'resolved_at',
    ];

    protected $casts = [
        'grid_state' => 'array',
        'objects_state' => 'array',
        'visibility_state' => 'array',
        'effects_state' => 'array',
        'runtime_state' => 'array',
        'loaded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function sceneTemplate(): BelongsTo
    {
        return $this->belongsTo(SceneTemplate::class);
    }

    public function actorInstances(): HasMany
    {
        return $this->hasMany(ActorInstance::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }
}

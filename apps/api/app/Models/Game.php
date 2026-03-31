<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'gm_user_id',
        'status',
        'active_scene_state_id',
        'started_at',
        'paused_at',
        'completed_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function gm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_user_id');
    }

    public function activeSceneState(): BelongsTo
    {
        return $this->belongsTo(GameSceneState::class, 'active_scene_state_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GameMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function sceneStates(): HasMany
    {
        return $this->hasMany(GameSceneState::class);
    }

    public function actorInstances(): HasMany
    {
        return $this->hasMany(ActorInstance::class);
    }
}

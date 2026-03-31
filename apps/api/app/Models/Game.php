<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Root aggregate for one campaign or play session.
 */
class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

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

    /**
     * Возвращает мастера, ответственного за игру.
     */
    public function gm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_user_id');
    }

    /**
     * Возвращает текущее активное состояние сцены игры.
     */
    public function activeSceneState(): BelongsTo
    {
        return $this->belongsTo(GameSceneState::class, 'active_scene_state_id');
    }

    /**
     * Возвращает строки участия пользователей в игре.
     */
    public function members(): HasMany
    {
        return $this->hasMany(GameMember::class, 'game_id', 'id');
    }

    /**
     * Возвращает пользователей, связанных с игрой через строки участия.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_members', 'game_id', 'user_id', 'id', 'id')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Возвращает все снимки состояний сцен, созданные для игры.
     */
    public function sceneStates(): HasMany
    {
        return $this->hasMany(GameSceneState::class, 'game_id', 'id');
    }

    /**
     * Возвращает всех runtime-акторов, созданных внутри игры.
     */
    public function actorInstances(): HasMany
    {
        return $this->hasMany(ActorInstance::class, 'game_id', 'id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Runtime scene snapshot that belongs to a specific game.
 */
class GameSceneState extends Model
{
    use HasFactory;

    protected $table = 'game_scene_states';

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

    /**
     * Возвращает игру, которой принадлежит это runtime-состояние сцены.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    /**
     * Возвращает исходный шаблон сцены, из которого построено состояние.
     */
    public function sceneTemplate(): BelongsTo
    {
        return $this->belongsTo(SceneTemplate::class, 'scene_template_id', 'id');
    }

    /**
     * Возвращает runtime-акторов, которые сейчас присутствуют в состоянии сцены.
     */
    public function actorInstances(): HasMany
    {
        return $this->hasMany(ActorInstance::class, 'game_scene_state_id', 'id');
    }

    /**
     * Возвращает encounter, созданные поверх этого состояния сцены.
     */
    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class, 'game_scene_state_id', 'id');
    }
}

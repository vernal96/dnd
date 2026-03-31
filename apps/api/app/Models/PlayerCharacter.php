<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Persistent player-owned character that survives between games.
 */
class PlayerCharacter extends Model
{
    use HasFactory;

    protected $table = 'player_characters';

    protected $fillable = [
        'user_id',
        'name',
        'race',
        'class',
        'level',
        'experience',
        'status',
        'base_stats',
        'derived_stats',
        'unlocked_skills',
        'meta',
    ];

    protected $casts = [
        'base_stats' => 'array',
        'derived_stats' => 'array',
        'unlocked_skills' => 'array',
        'meta' => 'array',
    ];

    /**
     * Возвращает пользователя, которому принадлежит постоянный персонаж.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Возвращает постоянную историю прогрессии персонажа.
     */
    public function progressionEvents(): HasMany
    {
        return $this->hasMany(CharacterProgression::class, 'player_character_id', 'id');
    }

    /**
     * Возвращает runtime-инстансы акторов, созданные из этого персонажа.
     */
    public function actorInstances(): HasMany
    {
        return $this->hasMany(ActorInstance::class, 'player_character_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerCharacter extends Model
{
    use HasFactory;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function progressionEvents(): HasMany
    {
        return $this->hasMany(CharacterProgression::class);
    }

    public function actorInstances(): HasMany
    {
        return $this->hasMany(ActorInstance::class);
    }
}

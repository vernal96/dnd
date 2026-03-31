<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterProgression extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'player_character_id',
        'event_type',
        'level_before',
        'level_after',
        'experience_before',
        'experience_after',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function playerCharacter(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class);
    }
}

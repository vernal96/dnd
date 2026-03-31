<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail of permanent player character progression events.
 */
class CharacterProgression extends Model
{
    use HasFactory;

    protected $table = 'character_progression';

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

    /**
     * Возвращает персонажа игрока, которому принадлежит эта запись прогрессии.
     */
    public function playerCharacter(): BelongsTo
    {
        return $this->belongsTo(PlayerCharacter::class);
    }
}

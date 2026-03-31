<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Concrete participation record of one actor inside an encounter.
 */
class EncounterParticipant extends Model
{
    use HasFactory;

    protected $table = 'encounter_participants';

    protected $fillable = [
        'encounter_id',
        'actor_id',
        'team_id',
        'initiative',
        'turn_order',
        'joined_round',
        'is_hidden',
        'is_surprised',
        'movement_left',
        'action_available',
        'bonus_action_available',
        'reaction_available',
        'combat_result_state',
        'state',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'is_surprised' => 'boolean',
        'action_available' => 'boolean',
        'bonus_action_available' => 'boolean',
        'reaction_available' => 'boolean',
        'state' => 'array',
    ];

    /**
     * Возвращает encounter, которому принадлежит этот участник.
     */
    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class, 'encounter_id', 'id');
    }

    /**
     * Возвращает runtime-актора, представленного этим участником.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(ActorInstance::class, 'actor_id');
    }
}

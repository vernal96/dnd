<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterParticipant extends Model
{
    use HasFactory;

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

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(ActorInstance::class, 'actor_id');
    }
}

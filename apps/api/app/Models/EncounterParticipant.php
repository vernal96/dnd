<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Concrete participation record of one actor inside an encounter.
 *
 * @property int $id
 * @property int $encounter_id
 * @property int $actor_id
 * @property int|null $team_id
 * @property int|null $initiative
 * @property int|null $turn_order
 * @property int|null $joined_round
 * @property bool $is_hidden
 * @property bool $is_surprised
 * @property int|null $movement_left
 * @property bool|null $action_available
 * @property bool|null $bonus_action_available
 * @property bool|null $reaction_available
 * @property string|null $combat_result_state
 * @property array<string, mixed>|null $state
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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

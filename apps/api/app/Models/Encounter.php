<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Combat layer attached to a concrete game scene state.
 *
 * @property int $id
 * @property int $game_id
 * @property int $game_scene_state_id
 * @property int|null $current_participant_id
 * @property string $status
 * @property int $round
 * @property string|null $trigger_type
 * @property array<string, mixed>|null $payload
 * @property Carbon|null $started_at
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Encounter extends Model
{
	use HasFactory;

	protected $table = 'encounters';

	protected $fillable = [
		'game_id',
		'game_scene_state_id',
		'status',
		'round',
		'current_participant_id',
		'trigger_type',
		'payload',
		'started_at',
		'resolved_at',
	];

	protected $casts = [
		'payload' => 'array',
		'started_at' => 'datetime',
		'resolved_at' => 'datetime',
	];

	/**
	 * Возвращает игру, которой принадлежит encounter.
	 */
	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class, 'game_id', 'id');
	}

	/**
	 * Возвращает состояние сцены, на котором происходит encounter.
	 */
	public function sceneState(): BelongsTo
	{
		return $this->belongsTo(GameSceneState::class, 'game_scene_state_id');
	}

	/**
	 * Возвращает всех участников, связанных с encounter.
	 */
	public function participants(): HasMany
	{
		return $this->hasMany(EncounterParticipant::class, 'encounter_id', 'id');
	}

	/**
	 * Возвращает участника, чей ход сейчас активен.
	 */
	public function currentParticipant(): BelongsTo
	{
		return $this->belongsTo(EncounterParticipant::class, 'current_participant_id');
	}
}

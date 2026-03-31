<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Audit trail of permanent player character progression events.
 *
 * @property int $id
 * @property int $player_character_id
 * @property string $event_type
 * @property int|null $level_before
 * @property int|null $level_after
 * @property int|null $experience_before
 * @property int|null $experience_after
 * @property array<string, mixed>|null $payload
 * @property Carbon $occurred_at
 */
class CharacterProgression extends Model
{
	use HasFactory;

	public $timestamps = false;
	protected $table = 'character_progression';
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

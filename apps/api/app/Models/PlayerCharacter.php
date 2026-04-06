<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Persistent player-owned character that survives between games.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $race
 * @property string|null $subrace
 * @property string|null $class
 * @property int $level
 * @property int $experience
 * @property string $status
 * @property string $luck
 * @property array<string, mixed>|null $base_stats
 * @property array<string, mixed>|null $derived_stats
 * @property string|null $image_path
 * @property array<string, mixed>|null $unlocked_skills
 * @property array<string, mixed>|null $meta
 * @property string|null $image_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PlayerCharacter extends Model
{
	use HasFactory;

	protected $table = 'player_characters';

	protected $fillable = [
		'user_id',
		'name',
		'description',
		'race',
		'subrace',
		'class',
		'level',
		'experience',
		'status',
		'luck',
		'base_stats',
		'derived_stats',
		'image_path',
		'unlocked_skills',
		'meta',
	];

	protected $casts = [
		'base_stats' => 'array',
		'derived_stats' => 'array',
		'unlocked_skills' => 'array',
		'meta' => 'array',
	];

	protected $appends = [
		'image_url',
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

	/**
	 * Возвращает публичный URL изображения персонажа игрока из storage.
	 */
	public function getImageUrlAttribute(): ?string
	{
		if (!is_string($this->image_path) || $this->image_path === '') {
			return null;
		}

		return '/api/player/character-images/' . basename($this->image_path);
	}
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Persistent actor template inside one game master library for player and non-player characters.
 *
 * @property int $id
 * @property int $gm_user_id
 * @property string $kind
 * @property string $name
 * @property string|null $description
 * @property string|null $race
 * @property string|null $character_class
 * @property int $level
 * @property int $movement_speed
 * @property int|null $base_health
 * @property int|null $health_current
 * @property int|null $health_max
 * @property array<string, mixed>|null $stats
 * @property array<int, array<string, mixed>>|null $inventory
 * @property string|null $image_path
 * @property array<string, mixed>|null $meta
 * @property string|null $image_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Actor extends Model
{
	use HasFactory;

	protected $table = 'actors';

	protected $fillable = [
		'gm_user_id',
		'kind',
		'name',
		'description',
		'race',
		'character_class',
		'level',
		'movement_speed',
		'base_health',
		'health_current',
		'health_max',
		'stats',
		'inventory',
		'image_path',
		'meta',
	];

	protected $casts = [
		'level' => 'integer',
		'movement_speed' => 'integer',
		'base_health' => 'integer',
		'health_current' => 'integer',
		'health_max' => 'integer',
		'stats' => 'array',
		'inventory' => 'array',
		'meta' => 'array',
	];

	protected $appends = [
		'image_url',
	];

	/**
	 * Возвращает мастера, которому принадлежит этот persistent-актор.
	 */
	public function gameMaster(): BelongsTo
	{
		return $this->belongsTo(User::class, 'gm_user_id', 'id');
	}

	/**
	 * Возвращает публичный URL изображения актора из storage.
	 */
	public function getImageUrlAttribute(): ?string
	{
		if (!is_string($this->image_path) || $this->image_path === '') {
			return null;
		}

		return '/api/gm/actor-images/' . basename($this->image_path);
	}
}

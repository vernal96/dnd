<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Authored scene blueprint used as the source for runtime states.
 *
 * @property int $id
 * @property int|null $created_by
 * @property string $name
 * @property string|null $description
 * @property int $width
 * @property int $height
 * @property string $status
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SceneTemplate extends Model
{
	use HasFactory;

	protected $table = 'scene_templates';

	protected $fillable = [
		'created_by',
		'name',
		'description',
		'width',
		'height',
		'status',
		'metadata',
	];

	protected $casts = [
		'metadata' => 'array',
	];

	/**
	 * Возвращает автора, создавшего шаблон.
	 */
	public function author(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	/**
	 * Возвращает клетки ландшафта, из которых состоит сетка шаблона.
	 */
	public function cells(): HasMany
	{
		return $this->hasMany(SceneTemplateCell::class, 'scene_template_id', 'id');
	}

	/**
	 * Возвращает объекты сцены, размещенные на шаблоне.
	 */
	public function objects(): HasMany
	{
		return $this->hasMany(SceneObject::class, 'scene_template_id', 'id');
	}

	/**
	 * Возвращает размещения persistent-акторов на шаблоне.
	 */
	public function actorPlacements(): HasMany
	{
		return $this->hasMany(SceneActorPlacement::class, 'scene_template_id', 'id');
	}

	/**
	 * Возвращает runtime-состояния сцен, созданные из шаблона.
	 */
	public function sceneStates(): HasMany
	{
		return $this->hasMany(GameSceneState::class, 'scene_template_id', 'id');
	}
}

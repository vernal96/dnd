<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Authored placement of one persistent actor on a scene template.
 *
 * @property int $id
 * @property int $scene_template_id
 * @property int $actor_id
 * @property int $x
 * @property int $y
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class SceneActorPlacement extends Model
{
	use HasFactory;

	protected $table = 'scene_actor_placements';

	protected $fillable = [
		'scene_template_id',
		'actor_id',
		'x',
		'y',
	];

	/**
	 * Возвращает шаблон сцены, на котором размещен актор.
	 */
	public function sceneTemplate(): BelongsTo
	{
		return $this->belongsTo(SceneTemplate::class, 'scene_template_id', 'id');
	}

	/**
	 * Возвращает persistent-актора, размещенного на сцене.
	 */
	public function actor(): BelongsTo
	{
		return $this->belongsTo(Actor::class, 'actor_id', 'id');
	}
}

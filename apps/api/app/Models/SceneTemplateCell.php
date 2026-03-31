<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One terrain cell inside a scene template grid.
 *
 * @property int $id
 * @property int $scene_template_id
 * @property int $x
 * @property int $y
 * @property string $terrain_type
 * @property int $elevation
 * @property bool $is_passable
 * @property bool $blocks_vision
 * @property array<string, mixed>|null $props
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SceneTemplateCell extends Model
{
	use HasFactory;

	protected $table = 'scene_template_cells';

	protected $fillable = [
		'scene_template_id',
		'x',
		'y',
		'terrain_type',
		'elevation',
		'is_passable',
		'blocks_vision',
		'props',
	];

	protected $casts = [
		'is_passable' => 'boolean',
		'blocks_vision' => 'boolean',
		'props' => 'array',
	];

	/**
	 * Возвращает шаблон сцены, которому принадлежит клетка.
	 */
	public function sceneTemplate(): BelongsTo
	{
		return $this->belongsTo(SceneTemplate::class, 'scene_template_id', 'id');
	}
}

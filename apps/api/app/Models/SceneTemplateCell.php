<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One terrain cell inside a scene template grid.
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

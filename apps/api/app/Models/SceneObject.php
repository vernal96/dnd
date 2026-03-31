<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Interactive or static object defined on a scene template.
 *
 * @property int $id
 * @property int $scene_template_id
 * @property string $kind
 * @property string|null $name
 * @property int|null $x
 * @property int|null $y
 * @property int $width
 * @property int $height
 * @property bool $is_hidden
 * @property bool $is_interactive
 * @property array<string, mixed>|null $state
 * @property array<string, mixed>|null $trigger_rules
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SceneObject extends Model
{
    use HasFactory;

    protected $table = 'scene_objects';

    protected $fillable = [
        'scene_template_id',
        'kind',
        'name',
        'x',
        'y',
        'width',
        'height',
        'is_hidden',
        'is_interactive',
        'state',
        'trigger_rules',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'is_interactive' => 'boolean',
        'state' => 'array',
        'trigger_rules' => 'array',
    ];

    /**
     * Возвращает шаблон сцены, которому принадлежит этот объект.
     */
    public function sceneTemplate(): BelongsTo
    {
        return $this->belongsTo(SceneTemplate::class, 'scene_template_id', 'id');
    }
}

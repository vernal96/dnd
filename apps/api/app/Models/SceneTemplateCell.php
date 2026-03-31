<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneTemplateCell extends Model
{
    use HasFactory;

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

    public function sceneTemplate(): BelongsTo
    {
        return $this->belongsTo(SceneTemplate::class);
    }
}

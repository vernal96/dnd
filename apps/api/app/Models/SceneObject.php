<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneObject extends Model
{
    use HasFactory;

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

    public function sceneTemplate(): BelongsTo
    {
        return $this->belongsTo(SceneTemplate::class);
    }
}

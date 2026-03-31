<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SceneTemplate extends Model
{
    use HasFactory;

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cells(): HasMany
    {
        return $this->hasMany(SceneTemplateCell::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(SceneObject::class);
    }

    public function sceneStates(): HasMany
    {
        return $this->hasMany(GameSceneState::class);
    }
}

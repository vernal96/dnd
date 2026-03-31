<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pending or resolved invitation of a user into a game.
 *
 * @property int $id
 * @property int $game_id
 * @property int $gm_user_id
 * @property int $invited_user_id
 * @property string $token
 * @property string $status
 * @property Carbon|null $sent_at
 * @property Carbon|null $responded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GameInvitation extends Model
{
    use HasFactory;

    protected $table = 'game_invitations';

    protected $fillable = [
        'game_id',
        'gm_user_id',
        'invited_user_id',
        'token',
        'status',
        'sent_at',
        'responded_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Возвращает игру, к которой относится приглашение.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }

    /**
     * Возвращает мастера, который отправил приглашение.
     */
    public function gm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_user_id', 'id');
    }

    /**
     * Возвращает пользователя, которому адресовано приглашение.
     */
    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id', 'id');
    }
}

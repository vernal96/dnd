<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * User membership inside a concrete game.
 *
 * @property int $id
 * @property int $game_id
 * @property int $user_id
 * @property string $role
 * @property string $status
 * @property Carbon|null $joined_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GameMember extends Model
{
	use HasFactory;

	protected $table = 'game_members';

	protected $fillable = [
		'game_id',
		'user_id',
		'role',
		'status',
		'joined_at',
	];

	protected $casts = [
		'joined_at' => 'datetime',
	];

	/**
	 * Возвращает игру, на которую ссылается эта строка участия.
	 */
	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class, 'game_id', 'id');
	}

	/**
	 * Возвращает пользователя, привязанного к этой строке участия.
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}

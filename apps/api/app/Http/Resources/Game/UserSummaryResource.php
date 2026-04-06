<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует пользователя в краткое JSON-представление.
 *
 * @mixin User
 */
final class UserSummaryResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var User $user */
		$user = $this->resource;

		return [
			'id' => $user->id,
			'name' => $user->name,
			'email' => $user->email,
		];
	}
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use App\Data\Auth\AuthenticatedUserData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует DTO аутентифицированного пользователя в JSON.
 *
 * @mixin AuthenticatedUserData
 */
final class AuthenticatedUserResource extends JsonResource
{
	/**
	 * @return array{id: int, name: string, email: string, canAccessGm: bool}
	 */
	public function toArray(Request $request): array
	{
		/** @var AuthenticatedUserData $user */
		$user = $this->resource;

		return [
			'id' => $user->id,
			'name' => $user->name,
			'email' => $user->email,
			'canAccessGm' => $user->canAccessGm,
		];
	}
}

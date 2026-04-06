<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use App\Data\Auth\AuthSessionData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует DTO пользовательской сессии в JSON.
 *
 * @mixin AuthSessionData
 */
final class AuthSessionResource extends JsonResource
{
	/**
	 * @return array{authenticated: bool, user: ?array{id: int, name: string, email: string, canAccessGm: bool}, csrfToken: string}
	 */
	public function toArray(Request $request): array
	{
		/** @var AuthSessionData $session */
		$session = $this->resource;

		return [
			'authenticated' => $session->authenticated,
			'user' => $session->user !== null
				? AuthenticatedUserResource::make($session->user)->resolve($request)
				: null,
			'csrfToken' => $session->csrfToken,
		];
	}
}

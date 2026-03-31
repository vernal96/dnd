<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает данные для приглашения игрока в игровой стол.
 */
final readonly class InviteGameMemberData
{
	/**
	 * Создает DTO данных приглашения игрока.
	 */
	public function __construct(
		public string $login,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{login:string} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			login: $payload['login'],
		);
	}
}

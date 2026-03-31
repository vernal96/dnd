<?php

declare(strict_types=1);

namespace App\Data\Auth;

/**
 * Описывает данные входа пользователя.
 */
final readonly class LoginCredentialsData
{
	/**
	 * Создает DTO учетных данных для входа.
	 */
	public function __construct(
		public string $login,
		public string $password,
		public bool   $remember,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{login:string,password:string,remember?:bool} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			login: $payload['login'],
			password: $payload['password'],
			remember: $payload['remember'] ?? false,
		);
	}
}

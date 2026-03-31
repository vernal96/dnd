<?php

declare(strict_types=1);

namespace App\Data\Auth;

/**
 * Описывает данные регистрации пользователя.
 */
final readonly class RegisterUserData
{
	/**
	 * Создает DTO данных регистрации.
	 */
	public function __construct(
		public string $login,
		public string $email,
		public string $password,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{login:string,email:string,password:string} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			login: $payload['login'],
			email: $payload['email'],
			password: $payload['password'],
		);
	}
}

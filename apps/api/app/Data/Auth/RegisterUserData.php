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
        public string $heroName,
        public string $email,
        public string $password,
    ) {}

    /**
     * Создает DTO из валидированного payload.
     *
     * @param  array{hero_name:string,email:string,password:string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            heroName: $payload['hero_name'],
            email: $payload['email'],
            password: $payload['password'],
        );
    }
}

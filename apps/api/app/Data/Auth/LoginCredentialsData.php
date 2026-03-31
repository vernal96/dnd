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
        public string $email,
        public string $password,
        public bool $remember,
    ) {}

    /**
     * Создает DTO из валидированного payload.
     *
     * @param  array{email:string,password:string,remember?:bool}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            email: $payload['email'],
            password: $payload['password'],
            remember: $payload['remember'] ?? false,
        );
    }

    /**
     * Преобразует DTO в формат, ожидаемый guard для попытки входа.
     *
     * @return array{email:string,password:string}
     */
    public function toAuthAttempt(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Auth;

/**
 * Описывает данные запроса на восстановление пароля.
 */
final readonly class ForgotPasswordData
{
    /**
     * Создает DTO для запроса на восстановление пароля.
     */
    public function __construct(
        public string $email,
    ) {}

    /**
     * Создает DTO из валидированного payload.
     *
     * @param  array{email:string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            email: $payload['email'],
        );
    }
}

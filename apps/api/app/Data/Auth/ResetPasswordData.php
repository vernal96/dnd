<?php

declare(strict_types=1);

namespace App\Data\Auth;

/**
 * Описывает данные завершения сброса пароля.
 */
final readonly class ResetPasswordData
{
    /**
     * Создает DTO для завершения сброса пароля.
     */
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
        public string $passwordConfirmation,
    ) {}

    /**
     * Создает DTO из валидированного payload.
     *
     * @param  array{token:string,email:string,password:string,password_confirmation:string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            token: $payload['token'],
            email: $payload['email'],
            password: $payload['password'],
            passwordConfirmation: $payload['password_confirmation'],
        );
    }

    /**
     * Преобразует DTO в payload, ожидаемый password broker.
     *
     * @return array{token:string,email:string,password:string,password_confirmation:string}
     */
    public function toBrokerPayload(): array
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->passwordConfirmation,
        ];
    }
}

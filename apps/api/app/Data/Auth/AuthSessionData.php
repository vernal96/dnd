<?php

declare(strict_types=1);

namespace App\Data\Auth;

use App\Models\User;

/**
 * Описывает состояние пользовательской сессии.
 */
final readonly class AuthSessionData
{
    /**
     * Создает DTO состояния пользовательской сессии.
     */
    public function __construct(
        public bool $authenticated,
        public ?AuthenticatedUserData $user,
        public string $csrfToken,
    ) {}

    /**
     * Создает DTO из текущего пользователя.
     */
    public static function fromUser(?User $user, string $csrfToken): self
    {
        if ($user === null) {
            return new self(
                authenticated: false,
                user: null,
                csrfToken: $csrfToken,
            );
        }

        return new self(
            authenticated: true,
            user: AuthenticatedUserData::fromModel($user),
            csrfToken: $csrfToken,
        );
    }

    /**
     * Преобразует DTO в массив для JSON-ответа.
     *
     * @return array{authenticated:bool,user:array{id:int,name:string,email:string,canAccessGm:bool}|null,csrfToken:string}
     */
    public function toArray(): array
    {
        return [
            'authenticated' => $this->authenticated,
            'user' => $this->user?->toArray(),
            'csrfToken' => $this->csrfToken,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Auth;

use App\Models\User;

/**
 * Описывает пользователя в ответах auth API.
 */
final readonly class AuthenticatedUserData
{
    /**
     * Создает DTO авторизованного пользователя.
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $canAccessGm,
    ) {}

    /**
     * Создает DTO из модели пользователя.
     */
    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->getKey(),
            name: $user->name,
            email: $user->email,
            canAccessGm: (bool) $user->can_access_gm,
        );
    }

    /**
     * Преобразует DTO в массив для JSON-ответа.
     *
     * @return array{id:int,name:string,email:string,canAccessGm:bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'canAccessGm' => $this->canAccessGm,
        ];
    }
}

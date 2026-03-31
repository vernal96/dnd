<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает данные для создания новой игры.
 */
final readonly class CreateGameData
{
    /**
     * Создает DTO данных новой игры.
     */
    public function __construct(
        public string $title,
        public ?string $description,
    ) {}

    /**
     * Создает DTO из валидированного payload.
     *
     * @param  array{title:string,description?:string|null}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            title: $payload['title'],
            description: $payload['description'] ?? null,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает данные для смены статуса игры.
 */
final readonly class UpdateGameStatusData
{
    /**
     * Создает DTO смены статуса игры.
     */
    public function __construct(
        public string $status,
    ) {}

    /**
     * Создает DTO из валидированного payload.
     *
     * @param  array{status:string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            status: $payload['status'],
        );
    }
}

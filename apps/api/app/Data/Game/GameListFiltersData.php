<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает фильтры списка игр для кабинета мастера.
 */
final readonly class GameListFiltersData
{
    /**
     * Создает DTO фильтров списка игр.
     */
    public function __construct(
        public ?string $status,
    ) {}

    /**
     * Создает DTO из query-параметров запроса.
     *
     * @param  array{status?:string|null}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            status: $payload['status'] ?? null,
        );
    }
}

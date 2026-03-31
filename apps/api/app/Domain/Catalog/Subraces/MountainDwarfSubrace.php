<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Subraces;

use App\Domain\Catalog\AbstractSubrace;

/**
 * Подраса горного дварфа.
 */
final class MountainDwarfSubrace extends AbstractSubrace
{
    /**
     * Возвращает код подрасы.
     */
    public function getCode(): string
    {
        return 'mountain-dwarf';
    }

    /**
     * Возвращает название подрасы.
     */
    public function getName(): string
    {
        return 'Горный дварф';
    }

    /**
     * Возвращает описание подрасы.
     */
    public function getDescription(): ?string
    {
        return 'Тяжеловооруженные дварфы крепостей и кузниц.';
    }

    /**
     * Возвращает порядок отображения подрасы.
     */
    public function getSortOrder(): int
    {
        return 20;
    }
}

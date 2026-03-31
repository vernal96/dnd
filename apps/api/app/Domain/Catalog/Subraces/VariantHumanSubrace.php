<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Subraces;

use App\Domain\Catalog\AbstractSubrace;

/**
 * Подраса вариативного человека.
 */
final class VariantHumanSubrace extends AbstractSubrace
{
    /**
     * Возвращает код подрасы.
     */
    public function getCode(): string
    {
        return 'variant-human';
    }

    /**
     * Возвращает название подрасы.
     */
    public function getName(): string
    {
        return 'Вариативный человек';
    }

    /**
     * Возвращает описание подрасы.
     */
    public function getDescription(): ?string
    {
        return 'Люди с усиленной гибкостью развития и ранней специализацией.';
    }

    /**
     * Возвращает порядок отображения подрасы.
     */
    public function getSortOrder(): int
    {
        return 10;
    }
}

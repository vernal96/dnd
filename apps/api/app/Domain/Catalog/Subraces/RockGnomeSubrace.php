<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Subraces;

use App\Domain\Catalog\AbstractSubrace;

/**
 * Подраса скального гнома.
 */
final class RockGnomeSubrace extends AbstractSubrace
{
    /**
     * Возвращает код подрасы.
     */
    public function getCode(): string
    {
        return 'rock-gnome';
    }

    /**
     * Возвращает название подрасы.
     */
    public function getName(): string
    {
        return 'Скальный гном';
    }

    /**
     * Возвращает описание подрасы.
     */
    public function getDescription(): ?string
    {
        return 'Изобретатели и ремесленники, любящие механизмы, минералы и мастерские.';
    }

}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Domain\Catalog\AbstractRace;

/**
 * Сущность расы полуэльфа.
 */
final class HalfElfRace extends AbstractRace
{
    /**
     * Возвращает код расы.
     */
    public function getCode(): string
    {
        return 'half-elf';
    }

    /**
     * Возвращает название расы.
     */
    public function getName(): string
    {
        return 'Полуэльф';
    }

    /**
     * Возвращает описание расы.
     */
    public function getDescription(): ?string
    {
        return 'Народ на стыке двух миров, сочетающий человеческую гибкость и эльфийскую утонченность.';
    }

}

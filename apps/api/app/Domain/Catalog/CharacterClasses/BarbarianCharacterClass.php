<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheBerserkerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheWildHeartCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheWorldTreeCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PathOfTheZealotCharacterSubclass;

/**
 * Сущность класса варвара.
 */
final class BarbarianCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'barbarian';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Варвар';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Яростный воин, полагающийся на силу, стойкость и боевое неистовство.';
    }

    /**
     * Возвращает подклассы варвара.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new PathOfTheBerserkerCharacterSubclass,
            new PathOfTheWildHeartCharacterSubclass,
            new PathOfTheWorldTreeCharacterSubclass,
            new PathOfTheZealotCharacterSubclass,
        ];
    }
}

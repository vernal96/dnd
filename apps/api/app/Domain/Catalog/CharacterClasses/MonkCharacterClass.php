<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfMercyCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfShadowCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheElementsCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarriorOfTheOpenHandCharacterSubclass;

/**
 * Сущность класса монаха.
 */
final class MonkCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'monk';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Монах';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Воин внутренней дисциплины, направляющий энергию тела и духа в сверхчеловеческое мастерство.';
    }

    /**
     * Возвращает подклассы монаха.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new WarriorOfMercyCharacterSubclass,
            new WarriorOfShadowCharacterSubclass,
            new WarriorOfTheElementsCharacterSubclass,
            new WarriorOfTheOpenHandCharacterSubclass,
        ];
    }
}

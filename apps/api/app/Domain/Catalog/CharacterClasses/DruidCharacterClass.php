<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheLandCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheMoonCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheSeaCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CircleOfTheStarsCharacterSubclass;

/**
 * Сущность класса друида.
 */
final class DruidCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'druid';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Друид';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Хранитель природных сил, использующий первобытную магию и меняющий облик.';
    }

    /**
     * Возвращает подклассы друида.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new CircleOfTheLandCharacterSubclass,
            new CircleOfTheMoonCharacterSubclass,
            new CircleOfTheSeaCharacterSubclass,
            new CircleOfTheStarsCharacterSubclass,
        ];
    }
}

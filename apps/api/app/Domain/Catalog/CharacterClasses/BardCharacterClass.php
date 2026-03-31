<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfDanceCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfGlamourCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfLoreCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CollegeOfValorCharacterSubclass;

/**
 * Сущность класса барда.
 */
final class BardCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'bard';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Бард';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Мастер вдохновения, магии и искусства, меняющий ход событий словом и мелодией.';
    }

    /**
     * Возвращает подклассы барда.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new CollegeOfDanceCharacterSubclass,
            new CollegeOfGlamourCharacterSubclass,
            new CollegeOfLoreCharacterSubclass,
            new CollegeOfValorCharacterSubclass,
        ];
    }
}

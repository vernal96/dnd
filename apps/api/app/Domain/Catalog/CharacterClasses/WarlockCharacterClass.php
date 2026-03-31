<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ArchfeyPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\CelestialPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\FiendPatronCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\GreatOldOnePatronCharacterSubclass;

/**
 * Сущность класса колдуна.
 */
final class WarlockCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'warlock';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Колдун / Чернокнижник';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Заклинатель, получивший силу через договор с могущественным потусторонним покровителем.';
    }

    /**
     * Возвращает подклассы колдуна.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new ArchfeyPatronCharacterSubclass,
            new CelestialPatronCharacterSubclass,
            new FiendPatronCharacterSubclass,
            new GreatOldOnePatronCharacterSubclass,
        ];
    }
}

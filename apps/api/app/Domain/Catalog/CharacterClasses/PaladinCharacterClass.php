<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfDevotionCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfGloryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfTheAncientsCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\OathOfVengeanceCharacterSubclass;

/**
 * Сущность класса паладина.
 */
final class PaladinCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'paladin';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Паладин';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Священный воитель, следующий клятве и соединяющий веру, сталь и исцеляющий свет.';
    }

    /**
     * Возвращает подклассы паладина.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new OathOfDevotionCharacterSubclass,
            new OathOfGloryCharacterSubclass,
            new OathOfTheAncientsCharacterSubclass,
            new OathOfVengeanceCharacterSubclass,
        ];
    }
}

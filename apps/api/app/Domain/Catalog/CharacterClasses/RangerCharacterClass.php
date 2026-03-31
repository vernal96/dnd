<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\BeastMasterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\FeyWandererCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\GloomStalkerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\HunterCharacterSubclass;

/**
 * Сущность класса следопыта.
 */
final class RangerCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'ranger';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Следопыт';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Охотник и разведчик приграничья, совмещающий меткость, выживание и магию пути.';
    }

    /**
     * Возвращает подклассы следопыта.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new BeastMasterCharacterSubclass,
            new FeyWandererCharacterSubclass,
            new GloomStalkerCharacterSubclass,
            new HunterCharacterSubclass,
        ];
    }
}

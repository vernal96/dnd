<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\LifeDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\LightDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\TrickeryDomainCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WarDomainCharacterSubclass;

/**
 * Сущность класса жреца.
 */
final class ClericCharacterClass extends AbstractCharacterClass
{
    /**
     * Возвращает код класса персонажа.
     */
    public function getCode(): string
    {
        return 'cleric';
    }

    /**
     * Возвращает название класса персонажа.
     */
    public function getName(): string
    {
        return 'Жрец';
    }

    /**
     * Возвращает описание класса персонажа.
     */
    public function getDescription(): ?string
    {
        return 'Проводник божественной силы, сочетающий молитвы, поддержку и священное возмездие.';
    }

    /**
     * Возвращает подклассы жреца.
     *
     * @return list<AbstractCharacterSubclass>
     */
    public function getSubclasses(): array
    {
        return [
            new LifeDomainCharacterSubclass,
            new LightDomainCharacterSubclass,
            new TrickeryDomainCharacterSubclass,
            new WarDomainCharacterSubclass,
        ];
    }
}

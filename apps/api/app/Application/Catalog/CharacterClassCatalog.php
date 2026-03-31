<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\CharacterClasses\BarbarianCharacterClass;
use App\Domain\Catalog\CharacterClasses\BardCharacterClass;
use App\Domain\Catalog\CharacterClasses\ClericCharacterClass;
use App\Domain\Catalog\CharacterClasses\DruidCharacterClass;
use App\Domain\Catalog\CharacterClasses\FighterCharacterClass;
use App\Domain\Catalog\CharacterClasses\MonkCharacterClass;
use App\Domain\Catalog\CharacterClasses\PaladinCharacterClass;
use App\Domain\Catalog\CharacterClasses\RangerCharacterClass;
use App\Domain\Catalog\CharacterClasses\RogueCharacterClass;
use App\Domain\Catalog\CharacterClasses\SorcererCharacterClass;
use App\Domain\Catalog\CharacterClasses\WarlockCharacterClass;
use App\Domain\Catalog\CharacterClasses\WizardCharacterClass;

/**
 * Хранит кодовый справочник классов и подклассов персонажей.
 */
final class CharacterClassCatalog
{
    /**
     * Возвращает все активные классы персонажей справочника.
     *
     * @return list<AbstractCharacterClass>
     */
    public function getActiveClasses(): array
    {
        return array_values(array_filter(
            $this->getAllClasses(),
            static fn (AbstractCharacterClass $characterClass): bool => $characterClass->isActive(),
        ));
    }

    /**
     * Возвращает один активный класс персонажа по коду.
     */
    public function findActiveClassByCode(string $code): ?AbstractCharacterClass
    {
        foreach ($this->getActiveClasses() as $characterClass) {
            if ($characterClass->getCode() === $code) {
                return $characterClass;
            }
        }

        return null;
    }

    /**
     * Возвращает полный кодовый справочник классов персонажей.
     *
     * @return list<AbstractCharacterClass>
     */
    private function getAllClasses(): array
    {
        return [
            new BarbarianCharacterClass,
            new BardCharacterClass,
            new ClericCharacterClass,
            new DruidCharacterClass,
            new FighterCharacterClass,
            new MonkCharacterClass,
            new PaladinCharacterClass,
            new RangerCharacterClass,
            new RogueCharacterClass,
            new SorcererCharacterClass,
            new WarlockCharacterClass,
            new WizardCharacterClass,
        ];
    }
}

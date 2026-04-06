<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\CharacterClasses\BarbarianCharacterClass;
use App\Domain\Actor\CharacterClasses\BardCharacterClass;
use App\Domain\Actor\CharacterClasses\ClericCharacterClass;
use App\Domain\Actor\CharacterClasses\DruidCharacterClass;
use App\Domain\Actor\CharacterClasses\FighterCharacterClass;
use App\Domain\Actor\CharacterClasses\MonkCharacterClass;
use App\Domain\Actor\CharacterClasses\PaladinCharacterClass;
use App\Domain\Actor\CharacterClasses\RangerCharacterClass;
use App\Domain\Actor\CharacterClasses\RogueCharacterClass;
use App\Domain\Actor\CharacterClasses\SorcererCharacterClass;
use App\Domain\Actor\CharacterClasses\WarlockCharacterClass;
use App\Domain\Actor\CharacterClasses\WizardCharacterClass;

/**
 * Хранит кодовый справочник классов и подклассов персонажей.
 */
final class CharacterClassCatalog
{
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
	 * Возвращает один доступный игроку активный класс персонажа по коду.
	 */
	public function findPlayerSelectableClassByCode(string $code): ?AbstractCharacterClass
	{
		foreach ($this->getPlayerSelectableClasses() as $characterClass) {
			if ($characterClass->getCode() === $code) {
				return $characterClass;
			}
		}

		return null;
	}

	/**
	 * Возвращает все активные классы персонажей справочника.
	 *
	 * @return list<AbstractCharacterClass>
	 */
	public function getActiveClasses(): array
	{
		return array_values(array_filter(
			$this->getAllClasses(),
			static fn(AbstractCharacterClass $characterClass): bool => $characterClass->isActive(),
		));
	}

	/**
	 * Возвращает активные классы, доступные для выбора игроком.
	 *
	 * @return list<AbstractCharacterClass>
	 */
	public function getPlayerSelectableClasses(): array
	{
		return $this->getActiveClasses();
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

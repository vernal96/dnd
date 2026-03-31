<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ArcaneTricksterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AssassinCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\SoulknifeCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ThiefCharacterSubclass;

/**
 * Сущность класса плута.
 */
final class RogueCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'rogue';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Плут';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Хитрый специалист скрытности, ловкости и точечных ударов по уязвимым местам.';
	}

	/**
	 * Возвращает подклассы плута.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new ArcaneTricksterCharacterSubclass,
			new AssassinCharacterSubclass,
			new SoulknifeCharacterSubclass,
			new ThiefCharacterSubclass,
		];
	}
}

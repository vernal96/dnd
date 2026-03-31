<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AberrantSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ClockworkSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\DraconicSorceryCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\WildMagicCharacterSubclass;

/**
 * Сущность класса чародея.
 */
final class SorcererCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'sorcerer';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Чародей';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Носитель врожденной магии, чья сила исходит из крови, судьбы или иного внутреннего источника.';
	}

	/**
	 * Возвращает подклассы чародея.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new AberrantSorceryCharacterSubclass,
			new ClockworkSorceryCharacterSubclass,
			new DraconicSorceryCharacterSubclass,
			new WildMagicCharacterSubclass,
		];
	}
}

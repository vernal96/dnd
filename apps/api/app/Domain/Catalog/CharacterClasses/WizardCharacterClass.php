<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\AbjurerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\DivinerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\EvokerCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\IllusionistCharacterSubclass;

/**
 * Сущность класса волшебника.
 */
final class WizardCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'wizard';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Волшебник';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Учёный магии, добивающийся могущества дисциплиной, исследованиями и точным знанием заклинаний.';
	}

	/**
	 * Возвращает подклассы волшебника.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new AbjurerCharacterSubclass,
			new DivinerCharacterSubclass,
			new EvokerCharacterSubclass,
			new IllusionistCharacterSubclass,
		];
	}
}

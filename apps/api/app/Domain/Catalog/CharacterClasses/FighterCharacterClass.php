<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterClasses;

use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\AbstractCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\BattleMasterCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\ChampionCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\EldritchKnightCharacterSubclass;
use App\Domain\Catalog\CharacterSubclasses\PsiWarriorCharacterSubclass;

/**
 * Сущность класса воина.
 */
final class FighterCharacterClass extends AbstractCharacterClass
{
	/**
	 * Возвращает код класса персонажа.
	 */
	public function getCode(): string
	{
		return 'fighter';
	}

	/**
	 * Возвращает название класса персонажа.
	 */
	public function getName(): string
	{
		return 'Воин';
	}

	/**
	 * Возвращает описание класса персонажа.
	 */
	public function getDescription(): string
	{
		return 'Универсальный мастер боя, добивающийся победы тренировкой, дисциплиной и техникой.';
	}

	/**
	 * Возвращает подклассы воина.
	 *
	 * @return list<AbstractCharacterSubclass>
	 */
	public function getSubclasses(): array
	{
		return [
			new BattleMasterCharacterSubclass,
			new ChampionCharacterSubclass,
			new EldritchKnightCharacterSubclass,
			new PsiWarriorCharacterSubclass,
		];
	}
}

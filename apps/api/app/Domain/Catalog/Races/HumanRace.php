<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Domain\Catalog\AbstractRace;
use App\Domain\Catalog\AbstractSubrace;
use App\Domain\Catalog\Subraces\StandardHumanSubrace;
use App\Domain\Catalog\Subraces\VariantHumanSubrace;

/**
 * Сущность расы человека.
 */
final class HumanRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'human';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Человек';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Гибкая и универсальная раса, подходящая для большинства классов и стилей игры.';
	}

	/**
	 * Возвращает подрасы человека.
	 *
	 * @return list<AbstractSubrace>
	 */
	public function getSubraces(): array
	{
		return [
			new StandardHumanSubrace,
			new VariantHumanSubrace,
		];
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Защита варвара без доспеха.
 */
final class BarbarianUnarmoredDefenseSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'barbarian-unarmored-defense',
			name: 'Защита без доспеха',
			description: 'Пока варвар не носит броню, его базовый КД равен 10 + модификаторы Ловкости и Телосложения; щит использовать можно.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MonkUnarmoredDefenseSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'monk-unarmored-defense',
			name: 'Защита без доспеха',
			description: 'Пока монах не носит броню и щит, его базовый КД равен 10 + модификаторы Ловкости и Мудрости.',
		);
	}
}

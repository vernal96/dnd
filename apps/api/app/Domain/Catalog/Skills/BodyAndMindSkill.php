<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class BodyAndMindSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'body-and-mind',
			name: 'Тело и разум',
			description: 'Повышает Ловкость и Мудрость монаха на 4, максимум до 25, завершая путь физического и духовного совершенства.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;

final class FoeSlayerSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'foe-slayer',
			name: 'Истребитель врагов',
			description: 'Усиливает Hunter’s Mark, повышая кубик урона способности до d10.',
			rollDice: SkillRollDice::D10,
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

final class SecondWindSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'second-wind',
			name: 'Второе дыхание',
			description: 'Позволяет бонусным действием быстро восстановить хиты и послужит топливом для части тактических приёмов воина.',
			rollDice: SkillRollDice::D10,
			targetType: SkillTargetType::Self,
		);
	}
}

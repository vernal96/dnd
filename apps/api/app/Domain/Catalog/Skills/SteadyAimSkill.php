<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class SteadyAimSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'steady-aim',
			name: 'Точная наводка',
			description: 'Если плут не двигался, он может бонусным действием получить преимущество на следующую атаку этого хода ценой полной остановки.',
			targetType: SkillTargetType::Self,
		);
	}
}

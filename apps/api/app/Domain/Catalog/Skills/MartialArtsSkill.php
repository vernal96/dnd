<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

final class MartialArtsSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'martial-arts',
			name: 'Боевые искусства',
			description: 'Позволяет монаху использовать Ловкость для монашеских атак, усиливает безоружные удары и открывает бонусную атаку.',
			rollDice: SkillRollDice::D6,
			targetType: SkillTargetType::Current,
		);
	}
}

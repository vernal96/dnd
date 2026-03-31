<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

final class ImprovedCunningStrikeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'improved-cunning-strike',
			name: 'Улучшенный хитрый удар',
			description: 'Усиливает Cunning Strike, позволяя применять более опасные эффекты и лучше расходовать кубики Sneak Attack.',
			rollDice: SkillRollDice::D6,
			targetType: SkillTargetType::Current,
		);
	}
}

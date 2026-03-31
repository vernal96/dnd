<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

/**
 * Улучшенный жестокий удар.
 */
final class ImprovedBrutalStrikeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'improved-brutal-strike',
			name: 'Улучшенный жестокий удар',
			description: 'Усиливает Жестокий удар новыми эффектами, а на высших уровнях позволяет сочетать два эффекта и повышает дополнительный урон.',
			rollDice: SkillRollDice::D10,
			targetType: SkillTargetType::Current,
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

/**
 * Жестокий удар.
 */
final class BrutalStrikeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'brutal-strike',
			name: 'Жестокий удар',
			description: 'Позволяет отказаться от преимущества одной безрассудной атаки, чтобы при попадании нанести дополнительный урон и наложить особый эффект удара.',
			rollDice: SkillRollDice::D10,
			targetType: SkillTargetType::Current,
		);
	}
}

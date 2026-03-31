<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Контрочарование.
 */
final class CountercharmSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'countercharm',
			name: 'Контрочарование',
			description: 'Бард может действием создавать защитную мелодию, помогающую союзникам против Очарования и Испуга в пределах слышимости.',
			targetType: SkillTargetType::AreaOfEffect,
			radiusCells: 6,
		);
	}
}

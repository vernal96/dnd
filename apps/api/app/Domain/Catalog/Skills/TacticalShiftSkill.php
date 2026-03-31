<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class TacticalShiftSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'tactical-shift',
			name: 'Тактический шаг',
			description: 'Позволяет потратить Второе дыхание, чтобы переместиться без провоцирования атак по возможности.',
			targetType: SkillTargetType::Self,
		);
	}
}

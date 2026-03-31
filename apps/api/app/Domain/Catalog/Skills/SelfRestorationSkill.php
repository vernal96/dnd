<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class SelfRestorationSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'self-restoration',
			name: 'Самовосстановление',
			description: 'Позволяет действием снимать с себя часть отрицательных состояний через контроль тела и духа.',
			targetType: SkillTargetType::Self,
		);
	}
}

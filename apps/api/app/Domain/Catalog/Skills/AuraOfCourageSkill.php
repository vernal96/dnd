<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class AuraOfCourageSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'aura-of-courage',
			name: 'Аура отваги',
			description: 'Паладин и его союзники рядом получают защиту от состояния Испуг.',
			targetType: SkillTargetType::AreaOfEffect,
			radiusCells: 2,
		);
	}
}

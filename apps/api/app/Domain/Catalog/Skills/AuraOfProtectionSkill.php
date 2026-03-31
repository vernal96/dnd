<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class AuraOfProtectionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'aura-of-protection',
			name: 'Аура защиты',
			description: 'Вы и союзники рядом с паладином получаете бонус Харизмы к спасброскам.',
			targetType: SkillTargetType::AreaOfEffect,
			radiusCells: 2,
		);
	}
}

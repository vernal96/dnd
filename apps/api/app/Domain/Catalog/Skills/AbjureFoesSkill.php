<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class AbjureFoesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'abjure-foes',
			name: 'Изгнание врагов',
			description: 'Через Channel Divinity паладин может подавить и замедлить выбранных врагов священным присутствием.',
			targetType: SkillTargetType::AreaOfEffect,
			radiusCells: 12,
		);
	}
}

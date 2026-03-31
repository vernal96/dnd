<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class AuraExpansionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'aura-expansion',
			name: 'Расширение ауры',
			description: 'Расширяет защитные ауры паладина на значительно большую область.',
			targetType: SkillTargetType::AreaOfEffect,
			radiusCells: 6,
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class SuperiorDefenseSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'superior-defense',
			name: 'Высшая защита',
			description: 'Позволяет потратить фокус для временной устойчивости ко всем видам урона, кроме силового.',
			targetType: SkillTargetType::Self,
		);
	}
}

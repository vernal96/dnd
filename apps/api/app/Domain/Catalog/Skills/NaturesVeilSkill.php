<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class NaturesVeilSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'natures-veil',
			name: 'Покров природы',
			description: 'Позволяет следопыту сливаться с окружением и временно становиться незримым.',
			targetType: SkillTargetType::Self,
		);
	}
}

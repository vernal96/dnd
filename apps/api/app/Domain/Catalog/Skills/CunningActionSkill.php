<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class CunningActionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'cunning-action',
			name: 'Хитрое действие',
			description: 'Позволяет бонусным действием выполнять рывок, отход или скрытность, сохраняя темп и мобильность.',
			targetType: SkillTargetType::Self,
		);
	}
}

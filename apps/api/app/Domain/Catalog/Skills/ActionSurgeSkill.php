<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class ActionSurgeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'action-surge',
			name: 'Рывок действия',
			description: 'Позволяет получить дополнительное действие в свой ход для мощного рывка или серии приёмов.',
			targetType: SkillTargetType::Self,
		);
	}
}

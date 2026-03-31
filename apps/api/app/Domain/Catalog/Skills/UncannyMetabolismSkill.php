<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class UncannyMetabolismSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'uncanny-metabolism',
			name: 'Необычный метаболизм',
			description: 'Позволяет монаху быстро восстановить часть сил и вернуть очки фокуса в начале боя.',
			targetType: SkillTargetType::Self,
		);
	}
}

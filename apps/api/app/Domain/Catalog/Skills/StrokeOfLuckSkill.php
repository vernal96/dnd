<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class StrokeOfLuckSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'stroke-of-luck',
			name: 'Удар удачи',
			description: 'Позволяет превратить проваленный d20-тест в натуральные 20, когда это особенно нужно.',
			targetType: SkillTargetType::Self,
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Ярость варвара.
 */
final class RageSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'rage',
			name: 'Ярость',
			description: 'Варвар входит в ярость, получая стойкость к физическому урону, преимущество на проверки и спасброски Силы и бонус к урону силовыми атаками.',
			targetType: SkillTargetType::Self,
		);
	}
}

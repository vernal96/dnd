<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Неумолимая ярость.
 */
final class RelentlessRageSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'relentless-rage',
			name: 'Неумолимая ярость',
			description: 'Если варвар падает до 0 хитов в ярости и не умирает сразу, он может удержаться на ногах успешным спасброском Телосложения.',
			targetType: SkillTargetType::Self,
		);
	}
}

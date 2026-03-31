<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Дикий облик.
 */
final class WildShapeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'wild-shape',
			name: 'Дикий облик',
			description: 'Позволяет друиду принимать звериную форму, расходуя применения Wild Shape.',
			targetType: SkillTargetType::Self,
		);
	}
}

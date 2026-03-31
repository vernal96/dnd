<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Улучшенная стихийная ярость.
 */
final class ImprovedElementalFurySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'improved-elemental-fury',
			name: 'Улучшенная стихийная ярость',
			description: 'Усиливает бонусы Elemental Fury, делая удары и первобытную магию друида ещё опаснее.',
			targetType: SkillTargetType::Current,
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Инстинктивный рывок.
 */
final class InstinctivePounceSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'instinctive-pounce',
			name: 'Инстинктивный рывок',
			description: 'При входе в ярость варвар может сразу переместиться на половину своей скорости.',
			targetType: SkillTargetType::Self,
		);
	}
}

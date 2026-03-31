<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class LayOnHandsSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'lay-on-hands',
			name: 'Наложение рук',
			description: 'Даёт запас исцеляющей силы, который паладин может направить прикосновением на себя или другое существо.',
			targetType: SkillTargetType::Current,
		);
	}
}

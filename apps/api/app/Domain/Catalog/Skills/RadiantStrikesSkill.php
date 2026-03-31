<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

final class RadiantStrikesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'radiant-strikes',
			name: 'Лучистые удары',
			description: 'Каждый удар оружием паладина наполняется священным светом и наносит дополнительный лучистый урон.',
			rollDice: SkillRollDice::D8,
			targetType: SkillTargetType::Current,
		);
	}
}

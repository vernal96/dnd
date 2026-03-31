<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

final class SneakAttackSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'sneak-attack',
			name: 'Скрытая атака',
			description: 'Позволяет раз за ход добавлять дополнительный урон к точному удару по врагу, если выполнены условия для Sneak Attack.',
			rollDice: SkillRollDice::D6,
			targetType: SkillTargetType::Current,
		);
	}
}

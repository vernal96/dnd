<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

/**
 * Благословенные удары.
 */
final class BlessedStrikesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'blessed-strikes',
			name: 'Благословенные удары',
			description: 'Позволяет раз в ход добавить лучистый урон к оружейной атаке или заговору жреца.',
			rollDice: SkillRollDice::D8,
			targetType: SkillTargetType::Current,
		);
	}
}

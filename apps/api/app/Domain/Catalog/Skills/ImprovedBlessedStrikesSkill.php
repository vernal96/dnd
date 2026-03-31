<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

/**
 * Улучшенные благословенные удары.
 */
final class ImprovedBlessedStrikesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'improved-blessed-strikes',
			name: 'Улучшенные благословенные удары',
			description: 'Усиливает дополнительный лучистый урон, который жрец добавляет к атаке оружием или заговором.',
			rollDice: SkillRollDice::D8,
			targetType: SkillTargetType::Current,
		);
	}
}

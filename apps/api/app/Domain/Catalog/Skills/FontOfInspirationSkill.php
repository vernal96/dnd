<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;

/**
 * Источник вдохновения.
 */
final class FontOfInspirationSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'font-of-inspiration',
			name: 'Источник вдохновения',
			description: 'Восстанавливает Вдохновение барда после короткого отдыха и усиливает кубик вдохновения до d8.',
			rollDice: SkillRollDice::D8,
		);
	}
}

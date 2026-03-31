<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Улучшение характеристик или выбор подходящего таланта.
 */
final class AbilityScoreImprovementSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'ability-score-improvement',
			name: 'Улучшение характеристик',
			description: 'Позволяет повысить характеристики персонажа или взять подходящий талант вместо прямого увеличения значений.',
		);
	}
}

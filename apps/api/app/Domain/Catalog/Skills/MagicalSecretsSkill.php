<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;

/**
 * Магические секреты.
 */
final class MagicalSecretsSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'magical-secrets',
			name: 'Магические секреты',
			description: 'Позволяет включить в арсенал барда заклинания из других списков, а на более высоких уровнях усиливает кубик вдохновения.',
			rollDice: SkillRollDice::D10,
		);
	}
}

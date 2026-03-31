<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Боевой стиль.
 */
final class FightingStyleSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'fighting-style',
			name: 'Боевой стиль',
			description: 'Даёт постоянную боевую специализацию, усиливающую выбранный тип сражения.',
		);
	}
}

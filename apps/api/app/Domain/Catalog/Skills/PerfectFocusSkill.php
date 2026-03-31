<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class PerfectFocusSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'perfect-focus',
			name: 'Совершенный фокус',
			description: 'Если у монаха мало очков фокуса, он может восстановить запас в начале боя и поддерживать высокий темп сражения.',
		);
	}
}

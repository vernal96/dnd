<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class HeightenedFocusSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'heightened-focus',
			name: 'Обострённый фокус',
			description: 'Улучшает применение очков фокуса, усиливая ключевые техники монаха.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MonksFocusSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'monks-focus',
			name: 'Фокус монаха',
			description: 'Даёт очки фокуса и техники, вроде Flurry of Blows, Patient Defense и Step of the Wind, питаемые внутренней энергией.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class SlipperyMindSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'slippery-mind',
			name: 'Неуловимый разум',
			description: 'Даёт владение спасбросками Мудрости и помогает плуту избегать ментального контроля.',
		);
	}
}

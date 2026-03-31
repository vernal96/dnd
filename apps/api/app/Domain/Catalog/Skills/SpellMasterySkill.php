<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class SpellMasterySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'spell-mastery',
			name: 'Мастерство заклинаний',
			description: 'Позволяет выбрать заклинания низкого круга, которыми волшебник пользуется с почти безупречной лёгкостью.',
		);
	}
}

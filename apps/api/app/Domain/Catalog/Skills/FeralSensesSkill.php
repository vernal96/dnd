<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class FeralSensesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'feral-senses',
			name: 'Звериные чувства',
			description: 'Обостряет чувства следопыта настолько, что скрывающимся врагам становится гораздо труднее уйти от него.',
		);
	}
}

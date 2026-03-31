<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class PreciseHunterSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'precise-hunter',
			name: 'Точный охотник',
			description: 'Повышает точность и урон следопыта против отмеченной Hunter’s Mark цели.',
		);
	}
}

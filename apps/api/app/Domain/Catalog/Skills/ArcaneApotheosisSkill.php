<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ArcaneApotheosisSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'arcane-apotheosis',
			name: 'Арканная апофеоз',
			description: 'Поднимает врождённую магию чародея до почти божественного уровня и делает применение Метамагии ещё свободнее.',
		);
	}
}

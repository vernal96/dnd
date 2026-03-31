<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ThreeExtraAttacksSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'three-extra-attacks',
			name: 'Три дополнительные атаки',
			description: 'Воин может атаковать четыре раза вместо одной атаки при действии Attack.',
		);
	}
}

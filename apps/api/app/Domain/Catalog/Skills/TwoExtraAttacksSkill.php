<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class TwoExtraAttacksSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'two-extra-attacks',
			name: 'Две дополнительные атаки',
			description: 'Воин может атаковать трижды вместо одной атаки при действии Attack.',
		);
	}
}

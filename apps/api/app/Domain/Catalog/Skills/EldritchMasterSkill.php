<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class EldritchMasterSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'eldritch-master',
			name: 'Эльдрический мастер',
			description: 'Усиливает Magical Cunning, позволяя полностью восстанавливать ячейки магии договора.',
		);
	}
}

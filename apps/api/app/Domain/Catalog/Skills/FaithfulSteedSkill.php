<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class FaithfulSteedSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'faithful-steed',
			name: 'Верный скакун',
			description: 'Даёт постоянную подготовку Find Steed и одно бесплатное призывание священного ездового спутника за долгий отдых.',
		);
	}
}

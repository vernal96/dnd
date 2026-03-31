<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class UncannyDodgeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'uncanny-dodge',
			name: 'Невероятное уклонение',
			description: 'Реакцией уменьшает урон от атаки, которая попала по плуту.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Дополнительная атака при действии Attack.
 */
final class ExtraAttackSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'extra-attack',
			name: 'Дополнительная атака',
			description: 'Позволяет атаковать дважды вместо одной атаки при использовании действия Attack в свой ход.',
		);
	}
}

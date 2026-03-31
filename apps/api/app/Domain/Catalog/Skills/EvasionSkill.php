<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Уклонение от эффектов с Dexterity saving throw.
 */
final class EvasionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'evasion',
			name: 'Уклонение',
			description: 'При эффектах с спасброском Ловкости позволяет не получить урон при успехе и получить лишь половину при провале.',
		);
	}
}

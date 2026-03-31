<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Владение мастерствами оружия.
 */
final class WeaponMasterySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'weapon-mastery',
			name: 'Мастерство оружия',
			description: 'Позволяет применять мастерские свойства выбранных видов оружия и расширяет арсенал боевых приёмов.',
		);
	}
}

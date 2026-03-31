<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class DeflectEnergySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'deflect-energy',
			name: 'Отражение энергии',
			description: 'Расширяет отражение атак, позволяя ослаблять некоторые энергетические эффекты и направлять силу обратно.',
		);
	}
}

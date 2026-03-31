<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class TacticalMasterSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'tactical-master',
			name: 'Тактический мастер',
			description: 'Расширяет боевую гибкость, позволяя менять мастерские свойства оружия под ситуацию.',
		);
	}
}

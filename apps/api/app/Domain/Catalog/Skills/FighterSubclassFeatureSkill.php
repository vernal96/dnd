<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class FighterSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'fighter-subclass-feature',
			name: 'Особенность архетипа воина',
			description: 'Даёт очередную особенность выбранного боевого архетипа.',
		);
	}
}

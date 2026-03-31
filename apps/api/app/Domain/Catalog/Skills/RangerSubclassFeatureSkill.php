<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class RangerSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'ranger-subclass-feature',
			name: 'Особенность архетипа следопыта',
			description: 'Даёт очередную особенность выбранного архетипа следопыта.',
		);
	}
}

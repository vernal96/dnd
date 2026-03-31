<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class SorcererSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'sorcerer-subclass-feature',
			name: 'Особенность источника чародейства',
			description: 'Даёт очередную особенность выбранного источника чародейства.',
		);
	}
}

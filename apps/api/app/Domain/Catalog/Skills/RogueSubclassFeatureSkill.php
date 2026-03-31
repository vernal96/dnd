<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class RogueSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'rogue-subclass-feature',
			name: 'Особенность архетипа плута',
			description: 'Даёт очередную особенность выбранного архетипа плута.',
		);
	}
}

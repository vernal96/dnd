<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MonkSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'monk-subclass-feature',
			name: 'Особенность пути монаха',
			description: 'Даёт очередную особенность выбранного пути монаха.',
		);
	}
}

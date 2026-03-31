<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Очередная особенность пути варвара.
 */
final class BarbarianSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'barbarian-subclass-feature',
			name: 'Особенность пути варвара',
			description: 'Даёт очередную особенность выбранного варварского пути.',
		);
	}
}

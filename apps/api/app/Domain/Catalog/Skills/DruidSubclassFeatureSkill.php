<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Особенность круга друида.
 */
final class DruidSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'druid-subclass-feature',
			name: 'Особенность круга друида',
			description: 'Даёт очередную особенность выбранного друидического круга.',
		);
	}
}

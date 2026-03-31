<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Особенность домена жреца.
 */
final class ClericSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'cleric-subclass-feature',
			name: 'Особенность домена',
			description: 'Даёт очередную особенность выбранного божественного домена.',
		);
	}
}

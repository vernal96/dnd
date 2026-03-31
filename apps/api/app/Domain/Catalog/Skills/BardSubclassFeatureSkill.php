<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Особенность коллегии барда.
 */
final class BardSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'bard-subclass-feature',
			name: 'Особенность коллегии барда',
			description: 'Даёт очередную особенность выбранной коллегии барда.',
		);
	}
}

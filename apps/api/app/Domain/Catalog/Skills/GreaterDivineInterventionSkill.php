<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Великое божественное вмешательство.
 */
final class GreaterDivineInterventionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'greater-divine-intervention',
			name: 'Великое божественное вмешательство',
			description: 'Позволяет использовать Divine Intervention для сотворения Wish, хотя после этого способность восстанавливается дольше.',
		);
	}
}

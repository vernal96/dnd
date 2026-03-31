<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Божественное вмешательство.
 */
final class DivineInterventionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'divine-intervention',
			name: 'Божественное вмешательство',
			description: 'Позволяет воззвать к своему божеству и сотворить заранее выбранное божественное заклинание без расхода ячейки.',
		);
	}
}

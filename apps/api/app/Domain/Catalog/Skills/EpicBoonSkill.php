<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Эпический дар для персонажей высших уровней.
 */
final class EpicBoonSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'epic-boon',
			name: 'Эпический дар',
			description: 'Открывает доступ к эпическому дару или другому подходящему таланту для персонажа 19 уровня.',
		);
	}
}

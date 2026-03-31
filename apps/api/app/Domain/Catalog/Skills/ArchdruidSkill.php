<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Архидруид.
 */
final class ArchdruidSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'archdruid',
			name: 'Архидруид',
			description: 'Делает связь друида с природой почти неисчерпаемой, улучшая восстановление Wild Shape и устойчивость к магии.',
		);
	}
}

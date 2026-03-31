<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Друидический язык и знаки.
 */
final class DruidicSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'druidic',
			name: 'Друидический',
			description: 'Даёт знание тайного языка и знаков друидов, используемых для общения и скрытых посланий.',
		);
	}
}

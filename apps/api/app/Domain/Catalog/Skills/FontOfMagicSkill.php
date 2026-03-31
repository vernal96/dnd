<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class FontOfMagicSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'font-of-magic',
			name: 'Источник магии',
			description: 'Даёт очки чародейства и позволяет превращать их в ячейки заклинаний и обратно.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class EmpoweredStrikesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'empowered-strikes',
			name: 'Усиленные удары',
			description: 'Безоружные удары монаха считаются магическими и лучше пробивают защиту необычных существ.',
		);
	}
}

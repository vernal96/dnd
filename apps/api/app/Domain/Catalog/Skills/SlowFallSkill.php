<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class SlowFallSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'slow-fall',
			name: 'Замедление падения',
			description: 'Реакцией уменьшает урон от падения благодаря отработанной технике приземления.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class RelentlessHunterSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'relentless-hunter',
			name: 'Неумолимый охотник',
			description: 'Усиливает охоту на отмеченную добычу и помогает не выпускать цель из-под давления.',
		);
	}
}

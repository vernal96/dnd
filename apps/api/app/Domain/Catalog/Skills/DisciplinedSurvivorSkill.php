<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class DisciplinedSurvivorSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'disciplined-survivor',
			name: 'Дисциплинированный выживший',
			description: 'Даёт все спасброски и позволяет перебрасывать некоторые провалы за счёт совершенной внутренней дисциплины.',
		);
	}
}

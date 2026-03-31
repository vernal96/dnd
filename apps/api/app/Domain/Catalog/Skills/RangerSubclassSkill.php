<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class RangerSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'ranger-subclass',
			name: 'Архетип следопыта',
			description: 'Открывает выбор архетипа следопыта, который определяет охотничью специализацию.',
		);
	}
}

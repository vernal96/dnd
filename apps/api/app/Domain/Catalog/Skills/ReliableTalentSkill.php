<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ReliableTalentSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'reliable-talent',
			name: 'Надёжный талант',
			description: 'Повышает минимальный результат на проверках навыков, в которых плут владеет мастерством.',
		);
	}
}

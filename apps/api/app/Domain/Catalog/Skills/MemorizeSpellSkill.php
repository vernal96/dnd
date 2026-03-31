<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MemorizeSpellSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'memorize-spell',
			name: 'Запоминание заклинания',
			description: 'Позволяет быстро переупорядочить подготовленные заклинания, используя гибкость и дисциплину арканного обучения.',
		);
	}
}

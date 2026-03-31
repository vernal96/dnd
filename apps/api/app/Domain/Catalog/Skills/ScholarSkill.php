<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ScholarSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'scholar',
			name: 'Учёный',
			description: 'Углубляет академическую подготовку волшебника и расширяет его компетенцию в области знаний.',
		);
	}
}

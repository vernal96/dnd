<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ElusiveSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'elusive',
			name: 'Неуловимость',
			description: 'Пока плут не недееспособен, атаки по нему не получают преимущество из обычных источников.',
		);
	}
}

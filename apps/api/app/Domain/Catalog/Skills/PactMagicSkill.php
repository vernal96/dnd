<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class PactMagicSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'pact-magic',
			name: 'Магия договора',
			description: 'Даёт короткоотдыховые ячейки магии договора и уникальный способ колдовства, связанный с покровителем.',
		);
	}
}

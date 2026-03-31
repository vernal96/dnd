<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Дикий спутник.
 */
final class WildCompanionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'wild-companion',
			name: 'Дикий спутник',
			description: 'Позволяет потратить применение Wild Shape, чтобы призвать знакомого духа природы в облике зверя.',
		);
	}
}

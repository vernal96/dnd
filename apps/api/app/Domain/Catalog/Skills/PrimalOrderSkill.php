<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Первозданный порядок.
 */
final class PrimalOrderSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'primal-order',
			name: 'Первозданный порядок',
			description: 'Позволяет друиду выбрать уклон в магическое знание или боевую связь с природой.',
		);
	}
}

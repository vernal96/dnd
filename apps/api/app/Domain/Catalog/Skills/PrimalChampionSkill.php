<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Первобытный чемпион.
 */
final class PrimalChampionSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'primal-champion',
			name: 'Первобытный чемпион',
			description: 'Повышает Силу и Телосложение варвара на 4, максимум до 25, делая его воплощением первобытной мощи.',
		);
	}
}

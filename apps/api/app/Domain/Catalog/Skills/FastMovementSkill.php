<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Быстрое движение варвара.
 */
final class FastMovementSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'fast-movement',
			name: 'Быстрое движение',
			description: 'Повышает скорость варвара на 10 футов, если он не носит тяжёлую броню.',
		);
	}
}

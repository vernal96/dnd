<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Божественный порядок.
 */
final class DivineOrderSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'divine-order',
			name: 'Божественный порядок',
			description: 'Позволяет выбрать путь жреца между большей боевой подготовкой и углублённым служением знаниям и ритуалам.',
		);
	}
}

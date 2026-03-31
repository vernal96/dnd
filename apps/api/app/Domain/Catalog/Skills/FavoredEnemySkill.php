<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class FavoredEnemySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'favored-enemy',
			name: 'Избранный враг',
			description: 'Даёт постоянную подготовку Hunter’s Mark и удобные способы его применять без расхода обычных ресурсов.',
		);
	}
}

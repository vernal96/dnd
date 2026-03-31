<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Звериный инстинкт.
 */
final class FeralInstinctSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'feral-instinct',
			name: 'Звериный инстинкт',
			description: 'Даёт преимущество на инициативу благодаря отточенным инстинктам.',
		);
	}
}

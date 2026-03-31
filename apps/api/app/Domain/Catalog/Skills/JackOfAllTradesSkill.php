<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Мастер на все руки.
 */
final class JackOfAllTradesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'jack-of-all-trades',
			name: 'Мастер на все руки',
			description: 'Позволяет добавлять половину бонуса мастерства к проверкам характеристик, где бард не владеет соответствующим навыком.',
		);
	}
}

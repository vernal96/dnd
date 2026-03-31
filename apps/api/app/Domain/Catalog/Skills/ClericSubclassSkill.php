<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Выбор божественного домена.
 */
final class ClericSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'cleric-subclass',
			name: 'Божественный домен',
			description: 'Открывает выбор божественного домена, определяющего специализацию жреца.',
		);
	}
}

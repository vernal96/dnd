<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class SorcerousRestorationSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'sorcerous-restoration',
			name: 'Восстановление чародейства',
			description: 'Позволяет возвращать часть очков чародейства после короткого отдыха.',
		);
	}
}

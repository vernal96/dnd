<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class SorcererSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'sorcerer-subclass',
			name: 'Источник чародейства',
			description: 'Открывает выбор источника врождённой магии, питающего способности чародея.',
		);
	}
}

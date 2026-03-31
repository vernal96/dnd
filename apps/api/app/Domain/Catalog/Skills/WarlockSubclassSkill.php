<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class WarlockSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'warlock-subclass',
			name: 'Покровитель колдуна',
			description: 'Открывает выбор потустороннего покровителя, от которого колдун получает часть сил.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MonkSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'monk-subclass',
			name: 'Путь монаха',
			description: 'Открывает выбор традиции воина-монаха, определяющей дальнейшие техники и приёмы.',
		);
	}
}

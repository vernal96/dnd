<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class RogueSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'rogue-subclass',
			name: 'Архетип плута',
			description: 'Открывает выбор архетипа плута, определяющего методы скрытности и хитрости.',
		);
	}
}

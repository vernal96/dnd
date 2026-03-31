<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class PaladinSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'paladin-subclass',
			name: 'Клятва паладина',
			description: 'Открывает выбор священной клятвы, определяющей путь и дополнительные силы паладина.',
		);
	}
}

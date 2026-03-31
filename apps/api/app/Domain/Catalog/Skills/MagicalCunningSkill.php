<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MagicalCunningSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'magical-cunning',
			name: 'Магическая хитрость',
			description: 'Позволяет в минуту сосредоточения вернуть часть исчерпанных ячеек магии договора.',
		);
	}
}

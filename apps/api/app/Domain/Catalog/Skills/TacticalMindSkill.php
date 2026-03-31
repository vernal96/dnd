<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class TacticalMindSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'tactical-mind',
			name: 'Тактический ум',
			description: 'Позволяет потратить Второе дыхание для усиления проваленной проверки характеристики, связанной с боевой смекалкой.',
		);
	}
}

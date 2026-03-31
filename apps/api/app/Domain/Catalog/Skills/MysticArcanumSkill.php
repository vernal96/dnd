<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MysticArcanumSkill extends ConfiguredSkill
{
	public function __construct(private readonly int $spellLevel)
	{
		parent::__construct(
			code: sprintf('mystic-arcanum-%d', $spellLevel),
			name: sprintf('Мистический арканум %d круга', $spellLevel),
			description: sprintf('Даёт одно заклинание колдуна %d круга, которое можно сотворять раз за долгий отдых без расхода обычной ячейки магии договора.', $spellLevel),
		);
	}
}

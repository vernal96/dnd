<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Звериные заклинания.
 */
final class BeastSpellsSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'beast-spells',
			name: 'Звериные заклинания',
			description: 'Позволяет друиду произносить заклинания в форме зверя, сохраняя доступ к магии во время Wild Shape.',
		);
	}
}

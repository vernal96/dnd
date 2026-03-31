<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class SignatureSpellsSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'signature-spells',
			name: 'Фирменные заклинания',
			description: 'Закрепляет за волшебником несколько избранных заклинаний как его коронные приёмы, делая их особенно удобными в применении.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ThievesCantSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'thieves-cant',
			name: 'Воровской жаргон',
			description: 'Даёт тайный язык жестов, намёков и символов, используемый преступным миром и плутами.',
		);
	}
}

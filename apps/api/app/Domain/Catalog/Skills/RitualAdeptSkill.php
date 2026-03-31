<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class RitualAdeptSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'ritual-adept',
			name: 'Адепт ритуалов',
			description: 'Позволяет волшебнику особенно уверенно работать с ритуальными заклинаниями из своей книги.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Несокрушимая мощь.
 */
final class IndomitableMightSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'indomitable-might',
			name: 'Несокрушимая мощь',
			description: 'Если итог проверки или спасброска Силы ниже значения Силы, варвар может использовать само значение характеристики вместо результата.',
		);
	}
}

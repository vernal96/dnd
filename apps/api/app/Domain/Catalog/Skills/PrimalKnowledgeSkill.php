<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Первобытное знание.
 */
final class PrimalKnowledgeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'primal-knowledge',
			name: 'Первобытное знание',
			description: 'Даёт дополнительный навык и позволяет в ярости применять Силу для ряда обычно несиловых проверок.',
		);
	}
}

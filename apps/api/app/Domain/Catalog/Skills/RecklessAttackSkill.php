<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Безрассудная атака.
 */
final class RecklessAttackSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'reckless-attack',
			name: 'Безрассудная атака',
			description: 'Даёт преимущество на силовые атаки варвара до начала его следующего хода, но в ответ враги тоже получают преимущество по нему.',
		);
	}
}

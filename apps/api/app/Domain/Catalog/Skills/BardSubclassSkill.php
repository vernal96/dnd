<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Выбор коллегии барда.
 */
final class BardSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'bard-subclass',
			name: 'Коллегия барда',
			description: 'Открывает выбор коллегии, которая определяет дальнейшую специализацию барда.',
		);
	}
}

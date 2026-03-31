<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Выбор пути варвара.
 */
final class BarbarianSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'barbarian-subclass',
			name: 'Путь варвара',
			description: 'Открывает выбор варварского пути, который добавляет новые особенности на следующих уровнях.',
		);
	}
}

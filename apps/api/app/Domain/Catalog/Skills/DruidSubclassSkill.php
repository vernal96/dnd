<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Выбор круга друида.
 */
final class DruidSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'druid-subclass',
			name: 'Круг друида',
			description: 'Открывает выбор друидического круга, который задаёт дальнейшую специализацию.',
		);
	}
}

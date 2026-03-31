<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Удвоенное мастерство в выбранных навыках или инструментах.
 */
final class ExpertiseSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'expertise',
			name: 'Экспертиза',
			description: 'Удваивает бонус мастерства для выбранных навыков или инструментов, в которых персонаж уже обучен.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Неугасающая ярость.
 */
final class PersistentRageSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'persistent-rage',
			name: 'Неугасающая ярость',
			description: 'Ярость варвара длится до 10 минут без продления каждый раунд, а при броске инициативы можно восстановить все её применения один раз до отдыха.',
			targetType: SkillTargetType::Self,
		);
	}
}

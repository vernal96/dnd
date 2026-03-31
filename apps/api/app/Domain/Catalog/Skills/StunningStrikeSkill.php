<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class StunningStrikeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'stunning-strike',
			name: 'Ошеломляющий удар',
			description: 'После попадания монах может потратить фокус, чтобы попытаться ошеломить врага до конца его следующего хода.',
			targetType: SkillTargetType::Current,
		);
	}
}

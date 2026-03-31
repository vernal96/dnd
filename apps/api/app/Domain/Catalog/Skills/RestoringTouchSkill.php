<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class RestoringTouchSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'restoring-touch',
			name: 'Восстанавливающее прикосновение',
			description: 'Позволяет паладину действием касанием снимать часть тяжёлых состояний с себя или союзника.',
			targetType: SkillTargetType::Current,
		);
	}
}

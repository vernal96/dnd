<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class PaladinsSmiteSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'paladins-smite',
			name: 'Кара паладина',
			description: 'Даёт постоянную подготовку Divine Smite и одно бесплатное применение этой кары без ячейки заклинаний за долгий отдых.',
			targetType: SkillTargetType::Current,
		);
	}
}

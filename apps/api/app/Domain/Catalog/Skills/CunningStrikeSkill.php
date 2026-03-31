<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

final class CunningStrikeSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'cunning-strike',
			name: 'Хитрый удар',
			description: 'Позволяет обменивать часть кубиков Sneak Attack на дополнительные эффекты вроде отравления, толчка или обезоруживания.',
			rollDice: SkillRollDice::D6,
			targetType: SkillTargetType::Current,
		);
	}
}

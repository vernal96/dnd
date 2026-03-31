<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

final class DeviousStrikesSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'devious-strikes',
			name: 'Коварные удары',
			description: 'Открывает особенно жестокие варианты Cunning Strike, позволяя выбивать врага из строя ещё надёжнее.',
			rollDice: SkillRollDice::D6,
			targetType: SkillTargetType::Current,
		);
	}
}

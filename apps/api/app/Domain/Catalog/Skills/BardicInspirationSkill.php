<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

/**
 * Вдохновение барда.
 */
final class BardicInspirationSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'bardic-inspiration',
			name: 'Вдохновение барда',
			description: 'Позволяет бонусным действием вдохновить союзника, дав ему кубик вдохновения для проверки, атаки или спасброска.',
			rollDice: SkillRollDice::D6,
			targetType: SkillTargetType::Current,
		);
	}
}

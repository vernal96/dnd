<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;
use App\Domain\Catalog\SkillTargetType;

/**
 * Испепеление нежити.
 */
final class SearUndeadSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'sear-undead',
			name: 'Испепеление нежити',
			description: 'Добавляет лучистый урон существам, затронутым эффектом Turn Undead, усиливая изгнание нежити.',
			rollDice: SkillRollDice::D8,
			targetType: SkillTargetType::AreaOfEffect,
			radiusCells: 6,
		);
	}
}

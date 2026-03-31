<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Стихийная ярость друида.
 */
final class ElementalFurySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'elemental-fury',
			name: 'Стихийная ярость',
			description: 'Усиливает удары и магию друида первозданной стихией, добавляя дополнительный эффект к атакам или заговорам.',
			targetType: SkillTargetType::Current,
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

/**
 * Слова сотворения.
 */
final class WordsOfCreationSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'words-of-creation',
			name: 'Слова сотворения',
			description: 'Даёт постоянную подготовку Power Word Heal и Power Word Kill и позволяет поразить вторую цель рядом с первой.',
			targetType: SkillTargetType::Current,
		);
	}
}

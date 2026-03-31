<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Перечисляет типы целей навыков.
 */
enum SkillTargetType: string
{
	case Self = 'self';
	case Current = 'current';
	case AreaOfEffect = 'aoe';
}

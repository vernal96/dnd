<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Перечисляет кубики, используемые навыками и классовыми особенностями.
 */
enum SkillRollDice: string
{
	case D4 = 'd4';
	case D6 = 'd6';
	case D8 = 'd8';
	case D10 = 'd10';
	case D12 = 'd12';
}

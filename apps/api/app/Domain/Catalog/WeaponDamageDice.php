<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Перечисляет поддерживаемые кубики урона оружия.
 */
enum WeaponDamageDice: string
{
	case D4 = '1d4';
	case D6 = '1d6';
	case D8 = '1d8';
	case D10 = '1d10';
	case D12 = '1d12';
	case TwoD6 = '2d6';
}

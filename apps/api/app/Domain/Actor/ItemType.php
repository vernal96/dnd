<?php

declare(strict_types=1);

namespace App\Domain\Actor;

/**
 * Перечисляет допустимые типы предметов каталога.
 */
enum ItemType: string
{
	case MeleeWeapon = 'melee-weapon';
	case RangedWeapon = 'ranged-weapon';
	case Armor = 'armor';
	case Equipment = 'equipment';
	case Ammunition = 'ammunition';
}

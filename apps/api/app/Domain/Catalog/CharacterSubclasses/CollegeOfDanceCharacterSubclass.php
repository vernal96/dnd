<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс коллегии танца.
 */
final class CollegeOfDanceCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'college-of-dance';

	protected const string NAME = 'Коллегия танца';

	protected const ?string DESCRIPTION = 'Бард, превращающий движение и ритм в источник магии и поддержки.';
}

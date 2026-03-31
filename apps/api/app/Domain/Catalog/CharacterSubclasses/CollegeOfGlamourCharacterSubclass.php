<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс коллегии гламура.
 */
final class CollegeOfGlamourCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'college-of-glamour';

	protected const string NAME = 'Коллегия гламура';

	protected const ?string DESCRIPTION = 'Очаровывающий бард, вдохновленный магией Фейских Дворов.';
}

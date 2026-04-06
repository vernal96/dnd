<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс пути дикого сердца.
 */
final class PathOfTheWildHeartCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'path-of-the-wild-heart';

	protected const string NAME = 'Путь дикого сердца';

	protected const ?string DESCRIPTION = 'Путь, связывающий ярость варвара с первобытными звериными духами.';
}

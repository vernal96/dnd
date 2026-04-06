<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс пути фанатика.
 */
final class PathOfTheZealotCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'path-of-the-zealot';

	protected const string NAME = 'Путь фанатика';

	protected const ?string DESCRIPTION = 'Ярость варвара, усиленная религиозным или идеологическим экстазом.';
}

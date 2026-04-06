<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс воина открытой ладони.
 */
final class WarriorOfTheOpenHandCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'warrior-of-the-open-hand';

	protected const string NAME = 'Воин открытой ладони';

	protected const ?string DESCRIPTION = 'Классический мастер рукопашного боя, контроля и безупречной техники.';
}

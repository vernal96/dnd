<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс охотника.
 */
final class HunterCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'hunter';

	protected const string NAME = 'Охотник';

	protected const ?string DESCRIPTION = 'Следопыт, отточивший практичное искусство уничтожения опасной добычи.';
}

<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс охотника.
 */
final class HunterCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'hunter';

	protected const string NAME = 'Охотник';

	protected const ?string DESCRIPTION = 'Следопыт, отточивший практичное искусство уничтожения опасной добычи.';
}

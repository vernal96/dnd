<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс воина тени.
 */
final class WarriorOfShadowCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'warrior-of-shadow';

	protected const string NAME = 'Воин тени';

	protected const ?string DESCRIPTION = 'Монах скрытности и внезапности, растворяющийся в темноте.';
}

<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс воина стихий.
 */
final class WarriorOfTheElementsCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'warrior-of-the-elements';

	protected const string NAME = 'Воин стихий';

	protected const ?string DESCRIPTION = 'Монах, направляющий ки в потоки огня, ветра, земли и воды.';
}

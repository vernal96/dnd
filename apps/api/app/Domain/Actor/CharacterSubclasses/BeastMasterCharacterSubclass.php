<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс повелителя зверей.
 */
final class BeastMasterCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'beast-master';

	protected const string NAME = 'Повелитель зверей';

	protected const ?string DESCRIPTION = 'Следопыт, сражающийся бок о бок с верным звериным спутником.';
}

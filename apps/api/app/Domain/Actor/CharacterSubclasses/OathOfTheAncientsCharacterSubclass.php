<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс клятвы древних.
 */
final class OathOfTheAncientsCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'oath-of-the-ancients';

	protected const string NAME = 'Клятва древних';

	protected const ?string DESCRIPTION = 'Паладин, охраняющий свет жизни, природу и древнюю гармонию мира.';
}

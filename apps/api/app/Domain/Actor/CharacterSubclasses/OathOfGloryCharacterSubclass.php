<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс клятвы славы.
 */
final class OathOfGloryCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'oath-of-glory';

	protected const string NAME = 'Клятва славы';

	protected const ?string DESCRIPTION = 'Паладин героизма, великих свершений и вдохновляющего подвига.';
}

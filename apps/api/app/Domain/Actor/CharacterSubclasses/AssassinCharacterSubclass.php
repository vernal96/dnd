<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс убийцы.
 */
final class AssassinCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'assassin';

	protected const string NAME = 'Убийца';

	protected const ?string DESCRIPTION = 'Плут скрытного устранения, маскировки и смертельно точных атак.';
}

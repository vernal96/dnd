<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс коллегии доблести.
 */
final class CollegeOfValorCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'college-of-valor';

	protected const string NAME = 'Коллегия доблести';

	protected const ?string DESCRIPTION = 'Воинственный бард, ведущий союзников песней и личным примером.';
}

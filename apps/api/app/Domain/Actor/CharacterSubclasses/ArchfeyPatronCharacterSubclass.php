<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс архифеи-покровителя.
 */
final class ArchfeyPatronCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'archfey-patron';

	protected const string NAME = 'Архифея-покровитель';

	protected const ?string DESCRIPTION = 'Колдун, заключивший договор с могущественным существом Фейского мира.';
}

<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс Великого Древнего покровителя.
 */
final class GreatOldOnePatronCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'great-old-one-patron';

	protected const string NAME = 'Великий Древний покровитель';

	protected const ?string DESCRIPTION = 'Колдун, соприкоснувшийся с непостижимым разумом за пределами мира.';
}

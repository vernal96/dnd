<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс мистического ловкача.
 */
final class ArcaneTricksterCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'arcane-trickster';

	protected const string NAME = 'Мистический ловкач';

	protected const ?string DESCRIPTION = 'Плут, совмещающий ловкость рук, иллюзии и арканные уловки.';
}

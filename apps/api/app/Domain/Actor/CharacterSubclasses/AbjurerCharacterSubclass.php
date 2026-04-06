<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс оградителя.
 */
final class AbjurerCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'abjurer';

	protected const string NAME = 'Оградитель';

	protected const ?string DESCRIPTION = 'Волшебник защитной школы, преуспевающий в барьерах и отражении угроз.';
}

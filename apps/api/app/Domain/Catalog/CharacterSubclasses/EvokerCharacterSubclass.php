<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс воплотителя.
 */
final class EvokerCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'evoker';

	protected const string NAME = 'Воплотитель';

	protected const ?string DESCRIPTION = 'Волшебник разрушительных энергий, управляющий огнем, молнией и взрывами.';
}

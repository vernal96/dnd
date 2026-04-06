<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс прорицателя.
 */
final class DivinerCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'diviner';

	protected const string NAME = 'Прорицатель';

	protected const ?string DESCRIPTION = 'Волшебник, раскрывающий тайны будущего, знаков и скрытого знания.';
}

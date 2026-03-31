<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс странника Фей.
 */
final class FeyWandererCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'fey-wanderer';

	protected const string NAME = 'Странник Фей';

	protected const ?string DESCRIPTION = 'Следопыт, отмеченный чарами Фейского мира и его странной красотой.';
}

<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс клятвы преданности.
 */
final class OathOfDevotionCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'oath-of-devotion';

	protected const string NAME = 'Клятва преданности';

	protected const ?string DESCRIPTION = 'Паладин идеалов чести, праведности и безупречной верности свету.';
}

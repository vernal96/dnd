<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс клятвы мести.
 */
final class OathOfVengeanceCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'oath-of-vengeance';

	protected const string NAME = 'Клятва мести';

	protected const ?string DESCRIPTION = 'Паладин неумолимого воздаяния, преследующий великих злодеев.';
}

<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс домена обмана.
 */
final class TrickeryDomainCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'trickery-domain';

	protected const string NAME = 'Домен обмана';

	protected const ?string DESCRIPTION = 'Жрец хитрости, иллюзий, скрытности и божественных уловок.';
}

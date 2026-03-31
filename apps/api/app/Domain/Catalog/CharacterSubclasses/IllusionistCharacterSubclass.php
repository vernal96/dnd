<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс иллюзиониста.
 */
final class IllusionistCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'illusionist';

	protected const string NAME = 'Иллюзионист';

	protected const ?string DESCRIPTION = 'Волшебник обмана восприятия, туманных образов и ложной реальности.';
}

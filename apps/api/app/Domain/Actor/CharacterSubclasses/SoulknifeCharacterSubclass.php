<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс клинка души.
 */
final class SoulknifeCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'soulknife';

	protected const string NAME = 'Клинок души';

	protected const ?string DESCRIPTION = 'Плут, формирующий невидимые психические клинки силой разума.';
}

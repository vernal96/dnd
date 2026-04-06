<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс дикой магии.
 */
final class WildMagicCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'wild-magic';

	protected const string NAME = 'Дикая магия';

	protected const ?string DESCRIPTION = 'Чародей непредсказуемых выбросов силы и хаотических магических эффектов.';
}

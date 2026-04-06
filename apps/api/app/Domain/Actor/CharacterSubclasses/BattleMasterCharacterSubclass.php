<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс мастера боя.
 */
final class BattleMasterCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'battle-master';

	protected const string NAME = 'Мастер боя';

	protected const ?string DESCRIPTION = 'Воин-тактик, использующий маневры, контроль и мастерство оружия.';
}

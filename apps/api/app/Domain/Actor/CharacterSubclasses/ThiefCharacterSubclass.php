<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс вора.
 */
final class ThiefCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'thief';

	protected const string NAME = 'Вор';

	protected const ?string DESCRIPTION = 'Плут, специализирующийся на скорости, проникновении и ремесле кражи.';
}

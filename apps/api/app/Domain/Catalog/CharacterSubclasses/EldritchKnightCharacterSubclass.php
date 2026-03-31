<?php
declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс мистического рыцаря.
 */
final class EldritchKnightCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'eldritch-knight';

	protected const string NAME = 'Мистический рыцарь';

	protected const ?string DESCRIPTION = 'Воин, соединивший строевую школу боя с дисциплиной арканной магии.';
}

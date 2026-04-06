<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс круга земли.
 */
final class CircleOfTheLandCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'circle-of-the-land';

	protected const string NAME = 'Круг земли';

	protected const ?string DESCRIPTION = 'Друид, чья магия глубоко связана с конкретными ландшафтами мира.';
}

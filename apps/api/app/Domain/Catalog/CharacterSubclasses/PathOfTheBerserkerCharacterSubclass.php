<?php

declare(strict_types=1);

namespace App\Domain\Catalog\CharacterSubclasses;

use App\Domain\Catalog\AbstractCharacterSubclass;

/**
 * Подкласс пути берсерка.
 */
final class PathOfTheBerserkerCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'path-of-the-berserker';

	protected const string NAME = 'Путь берсерка';

	protected const ?string DESCRIPTION = 'Варвар, доводящий ярость до предельной разрушительной яркости.';
}

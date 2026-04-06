<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс круга луны.
 */
final class CircleOfTheMoonCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'circle-of-the-moon';

	protected const string NAME = 'Круг луны';

	protected const ?string DESCRIPTION = 'Друид, доводящий искусство превращения до вершины хищной мощи.';
}

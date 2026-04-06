<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс исчадия-покровителя.
 */
final class FiendPatronCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'fiend-patron';

	protected const string NAME = 'Исчадие-покровитель';

	protected const ?string DESCRIPTION = 'Колдун, получающий силу от демона, дьявола или иного инфернального владыки.';
}

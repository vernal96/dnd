<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс домена жизни.
 */
final class LifeDomainCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'life-domain';

	protected const string NAME = 'Домен жизни';

	protected const ?string DESCRIPTION = 'Жрец, посвященный исцелению, заботе и защите живых.';
}

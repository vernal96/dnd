<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс домена войны.
 */
final class WarDomainCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'war-domain';

	protected const string NAME = 'Домен войны';

	protected const ?string DESCRIPTION = 'Жрец битвы, благословляющий союзников и карающий врагов в бою.';
}

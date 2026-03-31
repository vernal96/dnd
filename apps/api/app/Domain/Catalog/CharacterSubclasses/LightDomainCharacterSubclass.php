<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс домена света.
 */
final class LightDomainCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'light-domain';

    protected const string NAME = 'Домен света';

    protected const ?string DESCRIPTION = 'Жрец сияния, огня и откровения, разящий тьму ослепительной силой.';
}

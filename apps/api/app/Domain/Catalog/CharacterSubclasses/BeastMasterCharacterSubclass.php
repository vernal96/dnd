<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс повелителя зверей.
 */
final class BeastMasterCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'beast-master';

    protected const string NAME = 'Повелитель зверей';

    protected const ?string DESCRIPTION = 'Следопыт, сражающийся бок о бок с верным звериным спутником.';
}

<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс сумрачного охотника.
 */
final class GloomStalkerCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'gloom-stalker';

    protected const string NAME = 'Сумрачный охотник';

    protected const ?string DESCRIPTION = 'Следопыт подземелий и темных рубежей, опасный в первом ударе.';
}

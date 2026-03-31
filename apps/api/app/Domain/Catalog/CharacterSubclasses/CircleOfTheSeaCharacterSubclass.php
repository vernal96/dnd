<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс круга моря.
 */
final class CircleOfTheSeaCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'circle-of-the-sea';

    protected const string NAME = 'Круг моря';

    protected const ?string DESCRIPTION = 'Друид волн, штормов и изменчивой силы прибрежной природы.';
}

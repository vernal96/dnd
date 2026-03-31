<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс небесного покровителя.
 */
final class CelestialPatronCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'celestial-patron';

    protected const string NAME = 'Небесный покровитель';

    protected const ?string DESCRIPTION = 'Колдун, наделенный силой светлого и высшего существа.';
}

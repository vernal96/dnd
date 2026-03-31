<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс искажённого колдовства.
 */
final class AberrantSorceryCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'aberrant-sorcery';

    protected const string NAME = 'Искажённое колдовство';

    protected const ?string DESCRIPTION = 'Чародей с чужеродной, псионической и тревожно измененной магией.';
}

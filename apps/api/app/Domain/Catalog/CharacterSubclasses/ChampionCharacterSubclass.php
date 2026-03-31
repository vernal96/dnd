<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс чемпиона.
 */
final class ChampionCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'champion';

    protected const string NAME = 'Чемпион';

    protected const ?string DESCRIPTION = 'Воин, доведший физическое совершенство и боевую надежность до предела.';
}

<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс коллегии знаний.
 */
final class CollegeOfLoreCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'college-of-lore';

    protected const string NAME = 'Коллегия знаний';

    protected const ?string DESCRIPTION = 'Бард-эрудит, собирающий тайны, истории и редкие магические приемы.';
}

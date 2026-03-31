<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс пути мирового древа.
 */
final class PathOfTheWorldTreeCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'path-of-the-world-tree';

    protected const string NAME = 'Путь мирового древа';

    protected const ?string DESCRIPTION = 'Варвар, черпающий силу из космической связи жизни, корней и роста.';
}

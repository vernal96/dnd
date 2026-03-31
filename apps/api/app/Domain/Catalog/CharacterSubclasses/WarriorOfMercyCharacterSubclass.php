<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс воина милосердия.
 */
final class WarriorOfMercyCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'warrior-of-mercy';

    protected const string NAME = 'Воин милосердия';

    protected const ?string DESCRIPTION = 'Монах, использующий ки для исцеления, боли и баланса между ними.';
}

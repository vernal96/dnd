<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс круга звёзд.
 */
final class CircleOfTheStarsCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'circle-of-the-stars';

    protected const string NAME = 'Круг звёзд';

    protected const ?string DESCRIPTION = 'Друид, читающий волю мироздания по созвездиям и небесным знакам.';
}

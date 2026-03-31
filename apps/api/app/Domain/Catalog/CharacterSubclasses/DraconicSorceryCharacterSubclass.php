<?php
declare(strict_types=1);
namespace App\Domain\Catalog\CharacterSubclasses;
use App\Domain\Catalog\AbstractCharacterSubclass;
/**
 * Подкласс драконьего колдовства.
 */
final class DraconicSorceryCharacterSubclass extends AbstractCharacterSubclass
{
    protected const string CODE = 'draconic-sorcery';

    protected const string NAME = 'Драконье колдовство';

    protected const ?string DESCRIPTION = 'Чародей, в чьей крови пробуждается мощь древних драконов.';
}

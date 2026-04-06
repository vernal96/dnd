<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс пси-воина.
 */
final class PsiWarriorCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'psi-warrior';

	protected const string NAME = 'Пси-воин';

	protected const ?string DESCRIPTION = 'Воин, усиливающий удары и защиту силой разума.';
}

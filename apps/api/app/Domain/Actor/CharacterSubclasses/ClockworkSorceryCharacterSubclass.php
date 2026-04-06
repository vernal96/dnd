<?php
declare(strict_types=1);

namespace App\Domain\Actor\CharacterSubclasses;

use App\Domain\Actor\AbstractCharacterSubclass;

/**
 * Подкласс часового колдовства.
 */
final class ClockworkSorceryCharacterSubclass extends AbstractCharacterSubclass
{
	protected const string CODE = 'clockwork-sorcery';

	protected const string NAME = 'Часовое колдовство';

	protected const ?string DESCRIPTION = 'Чародей, чья сила связана с порядком, механизмами и космической симметрией.';
}

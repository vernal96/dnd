<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Доступ к подготовке и использованию заклинаний класса.
 */
final class SpellcastingSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'spellcasting',
			name: 'Использование заклинаний',
			description: 'Открывает базовую магию класса: известные или подготовленные заклинания, ячейки и применение фокусировки.',
		);
	}
}

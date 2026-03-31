<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillRollDice;

/**
 * Высшее вдохновение.
 */
final class SuperiorInspirationSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'superior-inspiration',
			name: 'Высшее вдохновение',
			description: 'Усиливает кубик Вдохновения барда до d12 и позволяет быстрее восстанавливать ресурс в начале боя.',
			rollDice: SkillRollDice::D12,
		);
	}
}

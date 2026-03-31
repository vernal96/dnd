<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Направление божественной силы.
 */
final class ChannelDivinitySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'channel-divinity',
			name: 'Направление божественной силы',
			description: 'Позволяет расходовать божественную силу на особые эффекты класса, например Divine Spark, Turn Undead или Divine Sense у паладина.',
		);
	}
}

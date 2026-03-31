<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class EldritchInvocationsSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'eldritch-invocations',
			name: 'Мистические воззвания',
			description: 'Позволяет выбирать постоянные оккультные улучшения, меняющие магию и возможности колдуна.',
		);
	}
}

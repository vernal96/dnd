<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class DeflectAttacksSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'deflect-attacks',
			name: 'Отражение атак',
			description: 'Реакцией уменьшает урон от оружейной атаки по монаху, а иногда позволяет перенаправить силу удара.',
		);
	}
}

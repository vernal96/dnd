<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class TirelessSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'tireless',
			name: 'Неутомимый',
			description: 'Позволяет временно укреплять себя и лучше восстанавливаться от утомительных переходов.',
		);
	}
}

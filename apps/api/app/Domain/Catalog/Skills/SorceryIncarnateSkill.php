<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class SorceryIncarnateSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'sorcery-incarnate',
			name: 'Воплощённое чародейство',
			description: 'Усиливает Innate Sorcery, позволяя при необходимости активировать его за очки чародейства и сочетать две Метамагии на одном заклинании.',
			targetType: SkillTargetType::Self,
		);
	}
}

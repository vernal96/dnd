<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;
use App\Domain\Catalog\SkillTargetType;

final class InnateSorcerySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'innate-sorcery',
			name: 'Врождённое чародейство',
			description: 'Позволяет на время разжечь внутреннюю магию, усиливая заклинания чародея и его контроль над ними.',
			targetType: SkillTargetType::Self,
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class AcrobaticMovementSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'acrobatic-movement',
			name: 'Акробатическое движение',
			description: 'Даёт бег по вертикальным поверхностям и воде во время перемещения монаха.',
		);
	}
}

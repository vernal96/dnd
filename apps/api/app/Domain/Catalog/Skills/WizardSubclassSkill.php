<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class WizardSubclassSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'wizard-subclass',
			name: 'Школа волшебства',
			description: 'Открывает выбор школы волшебства, определяющей специализацию мага.',
		);
	}
}

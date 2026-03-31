<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class WizardSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'wizard-subclass-feature',
			name: 'Особенность школы волшебства',
			description: 'Даёт очередную особенность выбранной школы волшебства.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class PaladinSubclassFeatureSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'paladin-subclass-feature',
			name: 'Особенность клятвы паладина',
			description: 'Даёт очередную особенность выбранной клятвы паладина.',
		);
	}
}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class DeftExplorerSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'deft-explorer',
			name: 'Ловкий исследователь',
			description: 'Усиливает навыки следопыта вне боя, делая его лучше приспособленным к путешествиям и исследованию.',
		);
	}
}

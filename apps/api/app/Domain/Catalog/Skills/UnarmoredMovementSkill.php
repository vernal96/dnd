<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class UnarmoredMovementSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'unarmored-movement',
			name: 'Передвижение без доспеха',
			description: 'Увеличивает скорость монаха, пока он не носит броню и щит.',
		);
	}
}

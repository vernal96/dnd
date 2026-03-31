<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class IndomitableSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'indomitable',
			name: 'Несгибаемость',
			description: 'Позволяет перебросить проваленный спасбросок и устоять там, где обычный боец пал бы.',
		);
	}
}

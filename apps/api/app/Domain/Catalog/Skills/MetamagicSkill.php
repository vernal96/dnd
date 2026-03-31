<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class MetamagicSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'metamagic',
			name: 'Метамагия',
			description: 'Позволяет тратить очки чародейства на изменение формы, дальности, скорости и других параметров заклинаний.',
		);
	}
}

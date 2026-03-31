<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ArcaneRecoverySkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'arcane-recovery',
			name: 'Арканное восстановление',
			description: 'Позволяет после короткого отдыха вернуть часть потраченных ячеек заклинаний.',
		);
	}
}

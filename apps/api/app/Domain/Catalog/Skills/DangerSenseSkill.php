<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Чутье на опасность.
 */
final class DangerSenseSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'danger-sense',
			name: 'Чутьё на опасность',
			description: 'Даёт преимущество на спасброски Ловкости против заметных угроз, пока варвар не недееспособен.',
		);
	}
}

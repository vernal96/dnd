<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class ContactPatronSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'contact-patron',
			name: 'Связь с покровителем',
			description: 'Позволяет напрямую обратиться к покровителю через Contact Other Plane без риска провала спасброска.',
		);
	}
}

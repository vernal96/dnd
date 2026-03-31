<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

/**
 * Возрождение дикой силы.
 */
final class WildResurgenceSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'wild-resurgence',
			name: 'Возрождение дикой силы',
			description: 'Позволяет обменивать магические ресурсы и применения Wild Shape, гибко подпитывая природную магию друида.',
		);
	}
}

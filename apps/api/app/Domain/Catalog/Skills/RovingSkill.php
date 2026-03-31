<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class RovingSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'roving',
			name: 'Скиталец',
			description: 'Повышает скорость следопыта и добавляет лазание с плаванием для уверенного передвижения по дикой местности.',
		);
	}
}

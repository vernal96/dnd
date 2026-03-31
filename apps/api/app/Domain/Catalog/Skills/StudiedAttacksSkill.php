<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Skills;

use App\Domain\Catalog\ConfiguredSkill;

final class StudiedAttacksSkill extends ConfiguredSkill
{
	public function __construct()
	{
		parent::__construct(
			code: 'studied-attacks',
			name: 'Изученные атаки',
			description: 'Если воин промахивается, его последующие атаки по той же цели становятся точнее благодаря быстрой адаптации.',
		);
	}
}

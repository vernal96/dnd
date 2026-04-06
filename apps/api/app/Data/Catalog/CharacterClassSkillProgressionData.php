<?php

declare(strict_types=1);

namespace App\Data\Catalog;

use App\Domain\Actor\AbstractSkill;

/**
 * Хранит распределение навыков класса персонажа по уровням с 1 по 20.
 */
final readonly class CharacterClassSkillProgressionData
{
	/**
	 * Создает DTO прогрессии навыков по уровням.
	 *
	 * @param list<AbstractSkill> $level1
	 * @param list<AbstractSkill> $level2
	 * @param list<AbstractSkill> $level3
	 * @param list<AbstractSkill> $level4
	 * @param list<AbstractSkill> $level5
	 * @param list<AbstractSkill> $level6
	 * @param list<AbstractSkill> $level7
	 * @param list<AbstractSkill> $level8
	 * @param list<AbstractSkill> $level9
	 * @param list<AbstractSkill> $level10
	 * @param list<AbstractSkill> $level11
	 * @param list<AbstractSkill> $level12
	 * @param list<AbstractSkill> $level13
	 * @param list<AbstractSkill> $level14
	 * @param list<AbstractSkill> $level15
	 * @param list<AbstractSkill> $level16
	 * @param list<AbstractSkill> $level17
	 * @param list<AbstractSkill> $level18
	 * @param list<AbstractSkill> $level19
	 * @param list<AbstractSkill> $level20
	 */
	public function __construct(
		public array $level1 = [],
		public array $level2 = [],
		public array $level3 = [],
		public array $level4 = [],
		public array $level5 = [],
		public array $level6 = [],
		public array $level7 = [],
		public array $level8 = [],
		public array $level9 = [],
		public array $level10 = [],
		public array $level11 = [],
		public array $level12 = [],
		public array $level13 = [],
		public array $level14 = [],
		public array $level15 = [],
		public array $level16 = [],
		public array $level17 = [],
		public array $level18 = [],
		public array $level19 = [],
		public array $level20 = [],
	)
	{
	}

}

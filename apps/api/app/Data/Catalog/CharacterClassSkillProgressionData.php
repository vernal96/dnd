<?php

declare(strict_types=1);

namespace App\Data\Catalog;

use App\Domain\Catalog\AbstractSkill;

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

	/**
	 * Преобразует DTO в массив для API.
	 *
	 * @return array{
	 *     level1: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level2: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level3: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level4: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level5: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level6: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level7: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level8: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level9: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level10: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level11: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level12: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level13: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level14: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level15: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level16: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level17: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level18: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level19: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>,
	 *     level20: list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>
	 * }
	 */
	public function toArray(): array
	{
		return [
			'level1' => $this->serializeLevel($this->level1),
			'level2' => $this->serializeLevel($this->level2),
			'level3' => $this->serializeLevel($this->level3),
			'level4' => $this->serializeLevel($this->level4),
			'level5' => $this->serializeLevel($this->level5),
			'level6' => $this->serializeLevel($this->level6),
			'level7' => $this->serializeLevel($this->level7),
			'level8' => $this->serializeLevel($this->level8),
			'level9' => $this->serializeLevel($this->level9),
			'level10' => $this->serializeLevel($this->level10),
			'level11' => $this->serializeLevel($this->level11),
			'level12' => $this->serializeLevel($this->level12),
			'level13' => $this->serializeLevel($this->level13),
			'level14' => $this->serializeLevel($this->level14),
			'level15' => $this->serializeLevel($this->level15),
			'level16' => $this->serializeLevel($this->level16),
			'level17' => $this->serializeLevel($this->level17),
			'level18' => $this->serializeLevel($this->level18),
			'level19' => $this->serializeLevel($this->level19),
			'level20' => $this->serializeLevel($this->level20),
		];
	}

	/**
	 * Преобразует навыки одного уровня в массив ответа.
	 *
	 * @param list<AbstractSkill> $skills
	 * @return list<array{code: string, name: string, description: string, rollDice: ?string, targetType: ?string, radiusCells: ?int}>
	 */
	private function serializeLevel(array $skills): array
	{
		return array_map(
			static fn(AbstractSkill $skill): array => $skill->toArray(),
			$skills,
		);
	}
}

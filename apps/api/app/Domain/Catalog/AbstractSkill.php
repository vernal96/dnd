<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

use JsonSerializable;

/**
 * Базовая сущность навыка или классовой особенности.
 */
abstract class AbstractSkill implements JsonSerializable
{
	/**
	 * Возвращает код навыка.
	 */
	abstract public function getCode(): string;

	/**
	 * Возвращает название навыка.
	 */
	abstract public function getName(): string;

	/**
	 * Возвращает описание навыка.
	 */
	abstract public function getDescription(): string;

	/**
	 * Возвращает кубик навыка.
	 */
	public function getRollDice(): ?SkillRollDice
	{
		return null;
	}

	/**
	 * Возвращает тип цели навыка.
	 */
	public function getTargetType(): ?SkillTargetType
	{
		return null;
	}

	/**
	 * Возвращает радиус области эффекта в клетках.
	 */
	public function getRadiusCells(): ?int
	{
		return null;
	}

	/**
	 * Преобразует навык в массив для API.
	 *
	 * @return array{
	 *     code: string,
	 *     name: string,
	 *     description: string,
	 *     rollDice: ?string,
	 *     targetType: ?string,
	 *     radiusCells: ?int
	 * }
	 */
	public function toArray(): array
	{
		return [
			'code' => $this->getCode(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'rollDice' => $this->getRollDice()?->value,
			'targetType' => $this->getTargetType()?->value,
			'radiusCells' => $this->getRadiusCells(),
		];
	}

	/**
	 * Возвращает сериализуемое представление навыка.
	 *
	 * @return array{
	 *     code: string,
	 *     name: string,
	 *     description: string,
	 *     rollDice: ?string,
	 *     targetType: ?string,
	 *     radiusCells: ?int
	 * }
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}

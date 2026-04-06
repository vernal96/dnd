<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Базовая сущность навыка или классовой особенности.
 */
abstract class AbstractSkill
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
	public function getRollDice(): ?Dice
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
}

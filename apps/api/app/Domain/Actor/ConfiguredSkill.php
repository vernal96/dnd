<?php

declare(strict_types=1);

namespace App\Domain\Actor;

/**
 * Базовый конфигурируемый скилл, от которого наследуются конкретные способности.
 */
abstract class ConfiguredSkill extends AbstractSkill
{
	/**
	 * Создает скилл с фиксированными метаданными.
	 */
	public function __construct(
		private readonly string $code,
		private readonly string $name,
		private readonly string $description,
		private readonly ?Dice $rollDice = null,
		private readonly ?SkillTargetType $targetType = null,
		private readonly ?int $radiusCells = null,
	) {
	}

	/**
	 * Возвращает код скилла.
	 */
	final public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * Возвращает название скилла.
	 */
	final public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Возвращает описание скилла.
	 */
	final public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * Возвращает кубик скилла.
	 */
	final public function getRollDice(): ?Dice
	{
		return $this->rollDice;
	}

	/**
	 * Возвращает тип цели скилла.
	 */
	final public function getTargetType(): ?SkillTargetType
	{
		return $this->targetType;
	}

	/**
	 * Возвращает радиус области эффекта в клетках.
	 */
	final public function getRadiusCells(): ?int
	{
		return $this->radiusCells;
	}
}

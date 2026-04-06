<?php

declare(strict_types=1);

namespace App\Domain\Actor\Subraces;

use App\Domain\Actor\AbstractSubrace;

/**
 * Подраса лесного гнома.
 */
final class ForestGnomeSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'forest-gnome';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Лесной гном';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Скрытные гномы лесов, близкие к природе, зверям и мелкой магии.';
	}

}

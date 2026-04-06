<?php

declare(strict_types=1);

namespace App\Domain\Actor\Subraces;

use App\Domain\Actor\AbstractSubrace;

/**
 * Подраса дроу.
 */
final class DrowElfSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'drow';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Дроу (тёмный эльф)';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Эльфы подземных городов, скрытности, интриг и опасной грации.';
	}

}

<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Subraces;

use App\Domain\Catalog\AbstractSubrace;

/**
 * Подраса коренастого полурослика.
 */
final class StoutHalflingSubrace extends AbstractSubrace
{
	/**
	 * Возвращает код подрасы.
	 */
	public function getCode(): string
	{
		return 'stout-halfling';
	}

	/**
	 * Возвращает название подрасы.
	 */
	public function getName(): string
	{
		return 'Коренастый';
	}

	/**
	 * Возвращает описание подрасы.
	 */
	public function getDescription(): string
	{
		return 'Более крепкие и стойкие полурослики с выраженной выживаемостью.';
	}

}

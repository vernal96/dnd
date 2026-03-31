<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Races;

use App\Domain\Catalog\AbstractRace;

/**
 * Сущность расы тифлинга.
 */
final class TieflingRace extends AbstractRace
{
	/**
	 * Возвращает код расы.
	 */
	public function getCode(): string
	{
		return 'tiefling';
	}

	/**
	 * Возвращает название расы.
	 */
	public function getName(): string
	{
		return 'Тифлинг';
	}

	/**
	 * Возвращает описание расы.
	 */
	public function getDescription(): string
	{
		return 'Носители инфернального наследия, сочетающие внутреннюю силу, харизму и печать чуждости.';
	}

}

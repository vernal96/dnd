<?php

declare(strict_types=1);

namespace App\Domain\Actor;

/**
 * Описывает шкалу удачи актора при бросках кубика.
 */
enum LuckScale: string
{
	case Bad = 'bad';
	case Normal = 'normal';
	case Good = 'good';

	/**
	 * Возвращает допустимые строковые значения шкалы удачи.
	 *
	 * @return list<string>
	 */
	public static function values(): array
	{
		return array_map(
			static fn (self $scale): string => $scale->value,
			self::cases(),
		);
	}
}

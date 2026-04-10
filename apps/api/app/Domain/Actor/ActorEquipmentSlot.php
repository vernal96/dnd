<?php

declare(strict_types=1);

namespace App\Domain\Actor;

/**
 * Перечисляет слоты экипировки runtime-актора.
 */
enum ActorEquipmentSlot: string
{
	case MainHand = 'main_hand';
	case OffHand = 'off_hand';
	case Ranged = 'ranged';
	case Armor = 'armor';
	case AccessoryOne = 'accessory_1';
	case AccessoryTwo = 'accessory_2';

	/**
	 * Возвращает человекочитаемое название слота.
	 */
	public function label(): string
	{
		return match ($this) {
			self::MainHand => 'Основная рука',
			self::OffHand => 'Вторая рука',
			self::Ranged => 'Дальний бой',
			self::Armor => 'Доспех',
			self::AccessoryOne => 'Аксессуар 1',
			self::AccessoryTwo => 'Аксессуар 2',
		};
	}

	/**
	 * Возвращает список строковых значений слотов.
	 *
	 * @return list<string>
	 */
	public static function values(): array
	{
		return array_map(
			static fn (self $slot): string => $slot->value,
			self::cases(),
		);
	}
}

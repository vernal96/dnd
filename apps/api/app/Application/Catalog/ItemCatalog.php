<?php

declare(strict_types=1);

namespace App\Application\Catalog;

use App\Domain\Actor\Item;
use App\Domain\Actor\Items\BackpackItem;
use App\Domain\Actor\Items\ChainMailItem;
use App\Domain\Actor\Items\LongswordItem;
use App\Domain\Actor\Items\ShortbowItem;

/**
 * Хранит кодовый справочник предметов и снаряжения.
 */
final class ItemCatalog
{
	/**
	 * Возвращает один активный предмет по коду.
	 */
	public function findActiveItemByCode(string $code): ?Item
	{
		foreach ($this->getActiveItems() as $item) {
			if ($item->getCode() === $code) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Возвращает все активные предметы справочника.
	 *
	 * @return list<Item>
	 */
	public function getActiveItems(): array
	{
		return array_values(array_filter(
			$this->getAllItems(),
			static fn(Item $item): bool => $item->isActive(),
		));
	}

	/**
	 * Возвращает полный кодовый справочник предметов.
	 *
	 * @return list<Item>
	 */
	private function getAllItems(): array
	{
		return [
			new ChainMailItem,
			new LongswordItem,
			new ShortbowItem,
			new BackpackItem,
		];
	}
}

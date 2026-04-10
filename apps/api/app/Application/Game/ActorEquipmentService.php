<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Application\Catalog\ItemCatalog;
use App\Domain\Actor\ActorEquipmentSlot;
use App\Domain\Actor\Item;
use App\Domain\Actor\ItemType;
use App\Models\ActorInstance;
use RuntimeException;

/**
 * Управляет экипировкой runtime-актора поверх его инвентаря.
 */
final readonly class ActorEquipmentService
{
	/**
	 * Создает сервис экипировки runtime-актора.
	 */
	public function __construct(
		private ItemCatalog $itemCatalog,
	)
	{
	}

	/**
	 * Возвращает экипированный предмет в указанном слоте.
	 */
	public function resolveEquippedItem(ActorInstance $actorInstance, ActorEquipmentSlot $slot): ?Item
	{
		$inventoryEntry = $this->resolveEquippedInventoryEntry($actorInstance, $slot);

		if ($inventoryEntry === null) {
			return null;
		}

		$itemCode = $this->resolveInventoryItemCode($inventoryEntry);

		if ($itemCode === null) {
			return null;
		}

		return $this->itemCatalog->findActiveItemByCode($itemCode);
	}

	/**
	 * Возвращает первое экипированное оружие актора.
	 */
	public function resolveFirstEquippedWeapon(ActorInstance $actorInstance): ?Item
	{
		foreach ([ActorEquipmentSlot::MainHand, ActorEquipmentSlot::OffHand, ActorEquipmentSlot::Ranged] as $slot) {
			$item = $this->resolveEquippedItem($actorInstance, $slot);

			if ($item instanceof Item && $this->isWeapon($item)) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Экипирует предмет из инвентаря в слот или очищает слот.
	 *
	 * @throws RuntimeException
	 */
	public function equipRuntimeActor(ActorInstance $actorInstance, ActorEquipmentSlot $slot, ?string $itemCode): void
	{
		$inventory = $this->resolveInventory($actorInstance);

		if ($itemCode === null || $itemCode === '') {
			$actorInstance->forceFill([
				'runtime_state' => $this->replaceRuntimeInventory($actorInstance, $this->clearSlot($inventory, $slot)),
			])->save();

			return;
		}

		$item = $this->itemCatalog->findActiveItemByCode($itemCode);

		if (!$item instanceof Item) {
			throw new RuntimeException('Предмет для экипировки не найден.');
		}

		$this->assertItemFitsSlot($item, $slot);

		$itemWasFound = false;
		$updatedInventory = [];

		foreach ($inventory as $inventoryEntry) {
			$inventoryEntryCode = $this->resolveInventoryItemCode($inventoryEntry);
			$inventoryEntry['slot'] = ($inventoryEntry['slot'] ?? null) === $slot->value ? null : ($inventoryEntry['slot'] ?? null);

			if (!$itemWasFound && $inventoryEntryCode === $itemCode) {
				$inventoryEntry['slot'] = $slot->value;
				$itemWasFound = true;
			}

			$inventoryEntry['isEquipped'] = is_string($inventoryEntry['slot'] ?? null) && $inventoryEntry['slot'] !== '';
			$updatedInventory[] = $inventoryEntry;
		}

		if (!$itemWasFound) {
			throw new RuntimeException('Предмет отсутствует в инвентаре актора.');
		}

		$actorInstance->forceFill([
			'runtime_state' => $this->replaceRuntimeInventory($actorInstance, $updatedInventory),
		])->save();
	}

	/**
	 * Проверяет совместимость предмета со слотом экипировки.
	 *
	 * @throws RuntimeException
	 */
	public function assertItemFitsSlot(Item $item, ActorEquipmentSlot $slot): void
	{
		$isAllowed = match ($slot) {
			ActorEquipmentSlot::MainHand, ActorEquipmentSlot::OffHand => $item->getType() === ItemType::MeleeWeapon,
			ActorEquipmentSlot::Ranged => $item->getType() === ItemType::RangedWeapon,
			ActorEquipmentSlot::Armor => $item->getType() === ItemType::Armor,
			ActorEquipmentSlot::AccessoryOne, ActorEquipmentSlot::AccessoryTwo => $item->getType() === ItemType::Equipment,
		};

		if (!$isAllowed) {
			throw new RuntimeException('Предмет нельзя экипировать в выбранный слот.');
		}
	}

	/**
	 * Возвращает признак оружия.
	 */
	public function isWeapon(Item $item): bool
	{
		return in_array($item->getType(), [ItemType::MeleeWeapon, ItemType::RangedWeapon], true);
	}

	/**
	 * Возвращает entry инвентаря для указанного слота.
	 *
	 * @return array<string, mixed>|null
	 */
	private function resolveEquippedInventoryEntry(ActorInstance $actorInstance, ActorEquipmentSlot $slot): ?array
	{
		foreach ($this->resolveInventory($actorInstance) as $inventoryEntry) {
			if (($inventoryEntry['slot'] ?? null) === $slot->value) {
				return $inventoryEntry;
			}
		}

		return null;
	}

	/**
	 * Возвращает инвентарь runtime-актора.
	 *
	 * @return list<array<string, mixed>>
	 */
	private function resolveInventory(ActorInstance $actorInstance): array
	{
		$inventory = $actorInstance->runtime_state['inventory'] ?? [];

		if (!is_array($inventory)) {
			return [];
		}

		return array_values(array_filter($inventory, static fn (mixed $entry): bool => is_array($entry)));
	}

	/**
	 * Возвращает код предмета из entry инвентаря.
	 *
	 * @param array<string, mixed> $inventoryEntry
	 */
	private function resolveInventoryItemCode(array $inventoryEntry): ?string
	{
		$itemCode = $inventoryEntry['itemCode'] ?? $inventoryEntry['item_code'] ?? null;

		return is_string($itemCode) && $itemCode !== '' ? $itemCode : null;
	}

	/**
	 * Очищает указанный слот экипировки.
	 *
	 * @param list<array<string, mixed>> $inventory
	 * @return list<array<string, mixed>>
	 */
	private function clearSlot(array $inventory, ActorEquipmentSlot $slot): array
	{
		return array_map(
			static function (array $inventoryEntry) use ($slot): array {
				if (($inventoryEntry['slot'] ?? null) === $slot->value) {
					$inventoryEntry['slot'] = null;
				}

				$inventoryEntry['isEquipped'] = is_string($inventoryEntry['slot'] ?? null) && $inventoryEntry['slot'] !== '';

				return $inventoryEntry;
			},
			$inventory,
		);
	}

	/**
	 * Возвращает runtime_state с обновленным инвентарем.
	 *
	 * @param list<array<string, mixed>> $inventory
	 * @return array<string, mixed>
	 */
	private function replaceRuntimeInventory(ActorInstance $actorInstance, array $inventory): array
	{
		$runtimeState = is_array($actorInstance->runtime_state) ? $actorInstance->runtime_state : [];
		$runtimeState['inventory'] = $inventory;

		return $runtimeState;
	}
}

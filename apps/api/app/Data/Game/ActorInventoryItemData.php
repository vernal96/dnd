<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает одну запись предмета в инвентаре актора.
 */
final readonly class ActorInventoryItemData
{
	/**
	 * Создает DTO записи инвентаря актора.
	 *
	 * @param array<string, mixed>|null $state
	 */
	public function __construct(
		public string $itemCode,
		public int $quantity,
		public ?string $slot,
		public bool $isEquipped,
		public ?array $state,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{item_code:string,quantity?:int,slot?:?string,is_equipped?:bool,state?:array<string, mixed>|null} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			itemCode: $payload['item_code'],
			quantity: $payload['quantity'] ?? 1,
			slot: $payload['slot'] ?? null,
			isEquipped: $payload['is_equipped'] ?? false,
			state: $payload['state'] ?? null,
		);
	}

	/**
	 * Возвращает сериализуемое представление записи инвентаря.
	 *
	 * @return array{itemCode:string,quantity:int,slot:?string,isEquipped:bool,state:?array}
	 */
	public function toArray(): array
	{
		return [
			'itemCode' => $this->itemCode,
			'quantity' => $this->quantity,
			'slot' => $this->slot,
			'isEquipped' => $this->isEquipped,
			'state' => $this->state,
		];
	}
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Domain\Catalog\Ability;
use App\Domain\Catalog\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует предмет каталога в JSON.
 *
 * @mixin Item
 */
final class CatalogItemResource extends JsonResource
{
	/**
	 * @param callable|null $imageUrlResolver
	 */
	public function __construct($resource, private readonly mixed $imageUrlResolver = null)
	{
		parent::__construct($resource);
	}

	/**
	 * @return array<string,mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var Item $item */
		$item = $this->resource;

		return [
			'code' => $item->getCode(),
			'name' => $item->getName(),
			'type' => $item->getType()->value,
			'category' => $item->getCategory(),
			'damageDice' => $this->formatWeaponDice($item->getCode(), $item->getDamageDice()),
			'versatileDamageDice' => $this->formatWeaponDice($item->getCode(), $item->getVersatileDamageDice()),
			'attackAbilities' => array_map(
				static fn (Ability $ability): string => $ability->getCode(),
				$item->getAttackAbilities(),
			),
			'armorClassBase' => $item->getArmorClassBase(),
			'armorClassAbility' => $item->getArmorClassAbility()?->getCode(),
			'armorClassAbilityCap' => $item->getArmorClassAbilityCap(),
			'armorClassBonus' => $item->getArmorClassBonus(),
			'description' => $item->getDescription(),
			'image_url' => is_callable($this->imageUrlResolver) && is_string($item->image())
				? ($this->imageUrlResolver)($item->image())
				: null,
			'isActive' => $item->isActive(),
		];
	}

	/**
	 * Форматирует кость урона оружия для API.
	 */
	private function formatWeaponDice(string $itemCode, ?\App\Domain\Catalog\Dice $dice): ?string
	{
		if ($dice === null) {
			return null;
		}

		if ($itemCode === 'greatsword' && $dice->value === 6) {
			return '2d6';
		}

		return '1d' . $dice->value;
	}
}

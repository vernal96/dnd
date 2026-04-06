<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Data\Catalog\StartingEquipmentEntryData;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Domain\Catalog\Ability;
use App\Domain\Catalog\Items\BackpackItem;
use App\Domain\Catalog\Items\ChainMailItem;
use App\Domain\Catalog\Items\LongswordItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует класс персонажа в JSON.
 *
 * @mixin AbstractCharacterClass
 */
final class ActorCharacterClassResource extends JsonResource
{
	/**
	 * @return array<string,mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var AbstractCharacterClass $characterClass */
		$characterClass = $this->resource;
		$skills = $characterClass->getSkillsByLevel()->level1;
		$startingEquipment = $this->resolveCatalogPreviewStartingEquipment($characterClass);

		return [
			'code' => $characterClass->getCode(),
			'name' => $characterClass->getName(),
			'description' => $characterClass->getDescription(),
			'isActive' => $characterClass->isActive(),
			'isPlayerSelectable' => true,
			'abilityBonuses' => AbilityBonusesResource::make($characterClass->getAbilityBonuses())->resolve(),
			'defaultPointBuyAllocation' => AbilityBonusesResource::make($characterClass->getDefaultPointBuyAllocation())->resolve(),
			'primaryAbilities' => array_map(
				static fn (Ability $ability): array => ActorAbilityResource::make($ability)->resolve(),
				$characterClass->getPrimaryAbilities(),
			),
			'subclasses' => ActorCharacterSubclassResource::collection($characterClass->getActiveSubclasses())->resolve(),
			'skillsByLevel' => [
				'level1' => ActorSkillResource::collection(array_slice($skills, 0, 1))->resolve(),
			],
			'startingEquipment' => array_map(
				static fn (StartingEquipmentEntryData $entry): array => [
					'quantity' => $entry->quantity,
					'item' => CatalogItemResource::make($entry->getItem())->resolve(),
				],
				[$startingEquipment],
			),
		];
	}

	/**
	 * Возвращает одно preview-снаряжение только из сокращённого каталога предметов.
	 */
	private function resolveCatalogPreviewStartingEquipment(AbstractCharacterClass $characterClass): StartingEquipmentEntryData
	{
		$itemClass = match ($characterClass->getCode()) {
			'cleric' => ChainMailItem::class,
			'barbarian', 'fighter', 'paladin' => LongswordItem::class,
			default => BackpackItem::class,
		};

		return new StartingEquipmentEntryData(
			itemClass: $itemClass,
			quantity: 1,
		);
	}
}

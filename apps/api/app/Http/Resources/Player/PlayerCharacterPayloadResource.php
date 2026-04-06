<?php

declare(strict_types=1);

namespace App\Http\Resources\Player;

use App\Data\Player\PlayerCharacterViewData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует представление персонажа игрока в JSON.
 *
 * @mixin PlayerCharacterViewData
 */
final class PlayerCharacterPayloadResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var PlayerCharacterViewData $character */
		$character = $this->resource;

		return [
			'id' => $character->id,
			'user_id' => $character->userId,
			'name' => $character->name,
			'description' => $character->description,
			'race' => $character->race,
			'race_name' => $character->raceName,
			'subrace' => $character->subrace,
			'subrace_name' => $character->subraceName,
			'character_class' => $character->characterClass,
			'character_class_name' => $character->characterClassName,
			'level' => $character->level,
			'experience' => $character->experience,
			'status' => $character->status,
			'base_stats' => $character->baseStats,
			'derived_stats' => $character->derivedStats,
			'image_path' => $character->imagePath,
			'image_url' => $character->imageUrl,
			'active_game_id' => $character->activeGameId,
			'active_game_title' => $character->activeGameTitle,
			'is_available_for_join' => $character->isAvailableForJoin,
			'created_at' => $character->createdAt,
			'updated_at' => $character->updatedAt,
		];
	}
}

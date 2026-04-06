<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\GameMember;
use App\Models\PlayerCharacter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует строку участия в игре в JSON.
 *
 * @mixin GameMember
 */
final class GameMemberResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var GameMember $member */
		$member = $this->resource;

		return [
			'id' => $member->id,
			'role' => $member->role,
			'status' => $member->status,
			'joined_at' => $member->joined_at?->toJSON(),
			'user' => $this->whenLoaded(
				'user',
				fn (): array => UserSummaryResource::make($member->user)->resolve($request),
			),
			'player_character' => $this->whenLoaded('playerCharacter', function () use ($member): ?array {
				/** @var PlayerCharacter|null $playerCharacter */
				$playerCharacter = $member->playerCharacter;

				if ($playerCharacter === null) {
					return null;
				}

				return [
					'id' => $playerCharacter->id,
					'user_id' => $playerCharacter->user_id,
					'name' => $playerCharacter->name,
					'description' => $playerCharacter->description,
					'race' => $playerCharacter->race,
					'subrace' => $playerCharacter->subrace,
					'class' => $playerCharacter->class,
					'level' => $playerCharacter->level,
					'experience' => $playerCharacter->experience,
					'status' => $playerCharacter->status,
					'image_path' => $playerCharacter->image_path,
					'image_url' => $playerCharacter->image_url,
					'created_at' => $playerCharacter->created_at?->toJSON(),
					'updated_at' => $playerCharacter->updated_at?->toJSON(),
				];
			}),
		];
	}
}

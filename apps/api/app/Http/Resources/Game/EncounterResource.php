<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\Encounter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует encounter модели в JSON.
 *
 * @mixin Encounter
 */
final class EncounterResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var Encounter $encounter */
		$encounter = $this->resource;

		return [
			'id' => $encounter->id,
			'game_id' => $encounter->game_id,
			'game_scene_state_id' => $encounter->game_scene_state_id,
			'current_participant_id' => $encounter->current_participant_id,
			'status' => $encounter->status,
			'round' => $encounter->round,
			'trigger_type' => $encounter->trigger_type,
			'payload' => $encounter->payload,
			'started_at' => $encounter->started_at?->toJSON(),
			'resolved_at' => $encounter->resolved_at?->toJSON(),
			'participants' => $this->whenLoaded(
				'participants',
				fn (): array => EncounterParticipantResource::collection($encounter->participants)->resolve($request),
			),
		];
	}
}

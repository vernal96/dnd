<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\EncounterParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует участника encounter модели в JSON.
 *
 * @mixin EncounterParticipant
 */
final class EncounterParticipantResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var EncounterParticipant $participant */
		$participant = $this->resource;

		return [
			'id' => $participant->id,
			'encounter_id' => $participant->encounter_id,
			'actor_id' => $participant->actor_id,
			'team_id' => $participant->team_id,
			'initiative' => $participant->initiative,
			'turn_order' => $participant->turn_order,
			'joined_round' => $participant->joined_round,
			'is_hidden' => (bool) $participant->is_hidden,
			'is_surprised' => (bool) $participant->is_surprised,
			'movement_left' => $participant->movement_left,
			'action_available' => $participant->action_available,
			'bonus_action_available' => $participant->bonus_action_available,
			'reaction_available' => $participant->reaction_available,
			'combat_result_state' => $participant->combat_result_state,
			'state' => $participant->state,
			'actor' => $this->whenLoaded(
				'actor',
				fn (): array => ActorInstanceResource::make($participant->actor)->resolve($request),
			),
		];
	}
}

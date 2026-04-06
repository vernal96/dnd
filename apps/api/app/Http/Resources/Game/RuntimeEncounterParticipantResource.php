<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Data\Game\RuntimeEncounterParticipantData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует участника encounter runtime-сцены в JSON.
 *
 * @mixin RuntimeEncounterParticipantData
 */
final class RuntimeEncounterParticipantResource extends JsonResource
{
	/**
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		/** @var RuntimeEncounterParticipantData $participant */
		$participant = $this->resource;

		return [
			'id' => $participant->id,
			'actor_id' => $participant->actorId,
			'initiative' => $participant->initiative,
			'turn_order' => $participant->turnOrder,
			'joined_round' => $participant->joinedRound,
			'movement_left' => $participant->movementLeft,
			'action_available' => $participant->actionAvailable,
			'bonus_action_available' => $participant->bonusActionAvailable,
			'reaction_available' => $participant->reactionAvailable,
			'combat_result_state' => $participant->combatResultState,
			'actor' => $participant->actor !== null
				? ActorInstanceResource::make($participant->actor)->resolve($request)
				: null,
		];
	}
}

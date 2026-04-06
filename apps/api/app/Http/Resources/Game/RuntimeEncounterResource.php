<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Data\Game\RuntimeEncounterData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует active encounter runtime-сцены в JSON.
 *
 * @mixin RuntimeEncounterData
 */
final class RuntimeEncounterResource extends JsonResource
{
	/**
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		/** @var RuntimeEncounterData $encounter */
		$encounter = $this->resource;

		return [
			'id' => $encounter->id,
			'status' => $encounter->status,
			'round' => $encounter->round,
			'current_participant_id' => $encounter->currentParticipantId,
			'started_at' => $encounter->startedAt,
			'participants' => RuntimeEncounterParticipantResource::collection($encounter->participants)->resolve($request),
		];
	}
}

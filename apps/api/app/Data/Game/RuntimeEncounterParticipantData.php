<?php

declare(strict_types=1);

namespace App\Data\Game;

use App\Models\ActorInstance;

/**
 * Описывает участника активного encounter для API runtime-сцены.
 */
final readonly class RuntimeEncounterParticipantData
{
	/**
	 * Создает DTO участника encounter.
	 */
	public function __construct(
		public int $id,
		public int $actorId,
		public int $initiative,
		public int $turnOrder,
		public int $joinedRound,
		public int $movementLeft,
		public bool $actionAvailable,
		public bool $bonusActionAvailable,
		public bool $reactionAvailable,
		public string $combatResultState,
		public ?ActorInstance $actor,
	)
	{
	}
}

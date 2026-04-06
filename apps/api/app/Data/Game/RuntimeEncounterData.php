<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает активный encounter runtime-сцены для API.
 */
final readonly class RuntimeEncounterData
{
	/**
	 * @param list<RuntimeEncounterParticipantData> $participants
	 */
	public function __construct(
		public int $id,
		public string $status,
		public int $round,
		public ?int $currentParticipantId,
		public ?string $startedAt,
		public array $participants,
	)
	{
	}
}

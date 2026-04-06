<?php

declare(strict_types=1);

namespace App\Data\Game;

use App\Models\GameSceneState;

/**
 * Описывает подготовленное представление runtime-сцены для HTTP-ответа.
 */
final readonly class RuntimeSceneViewData
{
	/**
	 * @param list<RuntimeItemDropData> $itemDrops
	 */
	public function __construct(
		public GameSceneState $sceneState,
		public array $itemDrops,
		public ?RuntimeEncounterData $encounter,
	)
	{
	}
}

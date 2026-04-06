<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает одно authored-размещение актора на сцене.
 */
final readonly class SceneActorPlacementData
{
	/**
	 * Создает DTO размещения актора.
	 */
	public function __construct(
		public int $actorId,
		public int $x,
		public int $y,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{actor_id:int,x:int,y:int} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			actorId: $payload['actor_id'],
			x: $payload['x'],
			y: $payload['y'],
		);
	}

}

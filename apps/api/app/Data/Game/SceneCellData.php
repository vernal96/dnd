<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает одну authored-клетку сцены.
 */
final readonly class SceneCellData
{
	/**
	 * Создает DTO authored-клетки.
	 *
	 * @param array<string, mixed>|null $props
	 */
	public function __construct(
		public int $x,
		public int $y,
		public string $terrainType,
		public int $elevation,
		public bool $isPassable,
		public bool $blocksVision,
		public ?array $props,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{x:int,y:int,terrain_type:string,elevation?:int,is_passable?:bool,blocks_vision?:bool,props?:array<string,mixed>|null} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			x: $payload['x'],
			y: $payload['y'],
			terrainType: $payload['terrain_type'],
			elevation: $payload['elevation'] ?? 0,
			isPassable: $payload['is_passable'] ?? true,
			blocksVision: $payload['blocks_vision'] ?? false,
			props: $payload['props'] ?? null,
		);
	}

	/**
	 * Возвращает DTO в виде массива для сервисного слоя.
	 *
	 * @return array{x:int,y:int,terrainType:string,elevation:int,isPassable:bool,blocksVision:bool,props:?array}
	 */
	public function toArray(): array
	{
		return [
			'x' => $this->x,
			'y' => $this->y,
			'terrainType' => $this->terrainType,
			'elevation' => $this->elevation,
			'isPassable' => $this->isPassable,
			'blocksVision' => $this->blocksVision,
			'props' => $this->props,
		];
	}
}

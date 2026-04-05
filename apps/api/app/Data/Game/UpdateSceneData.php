<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает полное authored-состояние редактируемой сцены.
 */
final readonly class UpdateSceneData
{
	/**
	 * Создает DTO сохранения authored-сцены.
	 *
	 * @param array<string, mixed>|null $metadata
	 * @param array<int, array{x:int,y:int,terrainType:string,elevation:int,isPassable:bool,blocksVision:bool,props:?array}> $cells
	 * @param array<int, array{kind:string,name:?string,x:int,y:int,width:int,height:int,isHidden:bool,isInteractive:bool,state:?array}> $objects
	 */
	public function __construct(
		public string $name,
		public ?string $description,
		public int $width,
		public int $height,
		public ?array $metadata,
		public array $cells,
		public array $objects,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{name:string,description?:string|null,width:int,height:int,metadata?:array<string,mixed>|null,cells:array<int, array<string, mixed>>,objects?:array<int, array<string, mixed>>} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			name: $payload['name'],
			description: $payload['description'] ?? null,
			width: $payload['width'],
			height: $payload['height'],
			metadata: $payload['metadata'] ?? null,
			cells: array_map(
				static fn (array $cell): array => SceneCellData::fromArray($cell)->toArray(),
				$payload['cells'],
			),
			objects: array_map(
				static fn (array $object): array => SceneObjectData::fromArray($object)->toArray(),
				$payload['objects'] ?? [],
			),
		);
	}
}

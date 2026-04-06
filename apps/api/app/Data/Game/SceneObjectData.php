<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает один authored-объект сцены.
 */
final readonly class SceneObjectData
{
	/**
	 * Создает DTO authored-объекта.
	 *
	 * @param array<string, mixed>|null $state
	 */
	public function __construct(
		public string $kind,
		public ?string $name,
		public int $x,
		public int $y,
		public int $width,
		public int $height,
		public bool $isHidden,
		public bool $isInteractive,
		public ?array $state,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{kind:string,name?:string|null,x:int,y:int,width?:int,height?:int,is_hidden?:bool,is_interactive?:bool,state?:array<string,mixed>|null} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			kind: $payload['kind'],
			name: $payload['name'] ?? null,
			x: $payload['x'],
			y: $payload['y'],
			width: $payload['width'] ?? 1,
			height: $payload['height'] ?? 1,
			isHidden: $payload['is_hidden'] ?? false,
			isInteractive: $payload['is_interactive'] ?? true,
			state: $payload['state'] ?? null,
		);
	}

}

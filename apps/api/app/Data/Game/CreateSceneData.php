<?php

declare(strict_types=1);

namespace App\Data\Game;

/**
 * Описывает данные для создания новой authored-сцены.
 */
final readonly class CreateSceneData
{
	/**
	 * Создает DTO новой сцены.
	 *
	 * @param array<string, mixed>|null $metadata
	 */
	public function __construct(
		public string $name,
		public ?string $description,
		public int $width,
		public int $height,
		public ?array $metadata,
	)
	{
	}

	/**
	 * Создает DTO из валидированного payload.
	 *
	 * @param array{name:string,description?:string|null,width?:int,height?:int,metadata?:array<string,mixed>|null} $payload
	 */
	public static function fromArray(array $payload): self
	{
		return new self(
			name: $payload['name'],
			description: $payload['description'] ?? null,
			width: $payload['width'] ?? 6,
			height: $payload['height'] ?? 6,
			metadata: $payload['metadata'] ?? null,
		);
	}
}

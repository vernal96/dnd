<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность огня.
 */
final class FireSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'fire';
	}

	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Огонь',
			'is_passable' => false,
			'blocks_vision' => false,
			'tags' => ['hazard', 'damage'],
		];
	}
}

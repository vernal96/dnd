<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность льда.
 */
final class IceSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'ice';
	}

	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Лед',
			'is_passable' => true,
			'blocks_vision' => false,
			'tags' => ['slippery', 'cold'],
		];
	}
}

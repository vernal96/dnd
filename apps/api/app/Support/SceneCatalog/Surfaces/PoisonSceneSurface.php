<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Surfaces;

/**
 * Описывает поверхность яда.
 */
final class PoisonSceneSurface implements SceneSurfaceDefinition
{
	public function code(): string
	{
		return 'poison';
	}

	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Яд',
			'is_passable' => false,
			'blocks_vision' => false,
			'tags' => ['hazard', 'toxic'],
		];
	}
}

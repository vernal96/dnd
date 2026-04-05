<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog;

use App\Support\SceneCatalog\Surfaces\FireSceneSurface;
use App\Support\SceneCatalog\Surfaces\GrassSceneSurface;
use App\Support\SceneCatalog\Surfaces\IceSceneSurface;
use App\Support\SceneCatalog\Surfaces\PoisonSceneSurface;
use App\Support\SceneCatalog\Surfaces\SceneSurfaceDefinition;
use App\Support\SceneCatalog\Surfaces\SoilSceneSurface;
use App\Support\SceneCatalog\Surfaces\StoneSceneSurface;
use App\Support\SceneCatalog\Surfaces\WaterSceneSurface;

/**
 * Хранит серверный каталог доступных поверхностей сцены.
 */
final class SceneSurfaceCatalog
{
	/**
	 * Возвращает коды допустимых поверхностей.
	 *
	 * @return array<int, string>
	 */
	public static function codes(): array
	{
		return array_column(self::all(), 'code');
	}

	/**
	 * Возвращает полный серверный каталог поверхностей.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(): array
	{
		return array_map(
			static fn (SceneSurfaceDefinition $definition): array => $definition->toArray(),
			self::definitions(),
		);
	}

	/**
	 * Возвращает объектные определения поверхностей сцены.
	 *
	 * @return array<int, SceneSurfaceDefinition>
	 */
	private static function definitions(): array
	{
		return [
			new GrassSceneSurface(),
			new StoneSceneSurface(),
			new SoilSceneSurface(),
			new WaterSceneSurface(),
			new FireSceneSurface(),
			new PoisonSceneSurface(),
			new IceSceneSurface(),
		];
	}
}

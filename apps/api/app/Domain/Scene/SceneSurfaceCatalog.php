<?php

declare(strict_types=1);

namespace App\Domain\Scene;

use App\Domain\Scene\Surfaces\FireSceneSurface;
use App\Domain\Scene\Surfaces\GrassSceneSurface;
use App\Domain\Scene\Surfaces\IceSceneSurface;
use App\Domain\Scene\Surfaces\PoisonSceneSurface;
use App\Domain\Scene\Surfaces\SceneSurfaceDefinition;
use App\Domain\Scene\Surfaces\SoilSceneSurface;
use App\Domain\Scene\Surfaces\StoneSceneSurface;
use App\Domain\Scene\Surfaces\WaterSceneSurface;

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
		return array_map(
			static fn (SceneSurfaceDefinition $definition): string => $definition->code(),
			self::definitions(),
		);
	}

	/**
	 * Возвращает полный серверный каталог поверхностей.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(?callable $imageUrlResolver = null): array
	{
		return array_map(
			static fn (SceneSurfaceDefinition $definition): array => [
				'code' => $definition->code(),
				'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($definition->image()) : null,
				'name' => $definition->name(),
				'is_passable' => $definition->isPassable(),
				'blocks_vision' => $definition->blocksVision(),
				'tags' => $definition->tags(),
			],
			self::definitions(),
		);
	}

	/**
	 * Возвращает одну поверхность по коду.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function resolve(string $code, ?callable $imageUrlResolver = null): ?array
	{
		foreach (self::all($imageUrlResolver) as $surface) {
			if (($surface['code'] ?? null) === $code) {
				return $surface;
			}
		}

		return null;
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

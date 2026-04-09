<?php

declare(strict_types=1);

namespace App\Domain\Scene;

use App\Domain\Scene\Objects\BarrelSceneObject;
use App\Domain\Scene\Objects\BushSceneObject;
use App\Domain\Scene\Objects\HouseSceneObject;
use App\Domain\Scene\Objects\SceneObjectDefinition;

/**
 * Хранит серверный каталог authored-объектов сцены.
 */
final class SceneObjectCatalog
{
	/**
	 * Возвращает коды допустимых объектов.
	 *
	 * @return array<int, string>
	 */
	public static function codes(): array
	{
		return array_map(
			static fn (SceneObjectDefinition $definition): string => $definition->code(),
			self::definitions(),
		);
	}

	/**
	 * Возвращает полный серверный каталог объектов.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(?callable $imageUrlResolver = null): array
	{
		return array_map(
			static fn (SceneObjectDefinition $definition): array => [
				'code' => $definition->code(),
				'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($definition->image()) : null,
				'name' => $definition->name(),
				'width' => $definition->width(),
				'height' => $definition->height(),
				'is_interactive' => $definition->isInteractive(),
				'blocks_vision' => $definition->blocksVision(),
				'is_passable' => $definition->isPassable(),
				'tags' => $definition->tags(),
			],
			self::definitions(),
		);
	}

	/**
	 * Возвращает объектные определения authored-объектов.
	 *
	 * @return array<int, SceneObjectDefinition>
	 */
	private static function definitions(): array
	{
		return [
			new BushSceneObject(),
			new BarrelSceneObject(),
			new HouseSceneObject(),
		];
	}
}

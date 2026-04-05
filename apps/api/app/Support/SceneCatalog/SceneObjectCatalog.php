<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog;

use App\Support\SceneCatalog\Objects\BarrelSceneObject;
use App\Support\SceneCatalog\Objects\BushSceneObject;
use App\Support\SceneCatalog\Objects\SceneObjectDefinition;

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
		return array_column(self::all(), 'code');
	}

	/**
	 * Возвращает полный серверный каталог объектов.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(): array
	{
		return array_map(
			static fn (SceneObjectDefinition $definition): array => $definition->toArray(),
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
		];
	}
}

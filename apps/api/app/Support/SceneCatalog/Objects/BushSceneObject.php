<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Objects;

/**
 * Описывает authored-объект куста.
 */
final class BushSceneObject implements SceneObjectDefinition
{
	/**
	 * Возвращает код authored-объекта.
	 */
	public function code(): string
	{
		return 'bush';
	}

	/**
	 * Возвращает серверное представление authored-объекта.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		return [
			'code' => $this->code(),
			'name' => 'Куст',
			'width' => 1,
			'height' => 1,
			'is_interactive' => false,
			'blocks_vision' => true,
			'is_passable' => false,
			'tags' => ['nature', 'cover'],
		];
	}
}

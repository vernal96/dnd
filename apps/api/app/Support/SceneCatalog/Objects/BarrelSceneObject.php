<?php

declare(strict_types=1);

namespace App\Support\SceneCatalog\Objects;

/**
 * Описывает authored-объект бочки.
 */
final class BarrelSceneObject implements SceneObjectDefinition
{
	/**
	 * Возвращает код authored-объекта.
	 */
	public function code(): string
	{
		return 'barrel';
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
			'name' => 'Бочка',
			'width' => 1,
			'height' => 1,
			'is_interactive' => true,
			'blocks_vision' => false,
			'is_passable' => false,
			'tags' => ['container', 'interior'],
		];
	}
}

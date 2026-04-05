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

	public function image(): string
	{
		return 'barrel.png';
	}

	/**
	 * Возвращает серверное представление authored-объекта.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(?callable $imageUrlResolver = null): array
	{
		return [
			'code' => $this->code(),
			'image_url' => is_callable($imageUrlResolver) ? $imageUrlResolver($this->image()) : null,
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

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

	public function image(): string
	{
		return 'bush.png';
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

<?php

declare(strict_types=1);

namespace App\Domain\Scene\Surfaces;

use App\Data\Game\SurfaceEffectRuleData;
use App\Domain\Actor\Elements\ActorElementDefinition;

/**
 * Описывает серверный контракт поверхности сцены.
 */
interface SceneSurfaceDefinition
{
	/**
	 * Возвращает код поверхности.
	 */
	public function code(): string;

	/**
	 * Возвращает путь картинки поверхности.
	 */
	public function image(): string;

	/**
	 * Возвращает название поверхности.
	 */
	public function name(): string;

	/**
	 * Возвращает признак проходимости поверхности.
	 */
	public function isPassable(): bool;

	/**
	 * Возвращает признак блокировки обзора поверхностью.
	 */
	public function blocksVision(): bool;

	/**
	 * Возвращает связанную со средой стихию поверхности.
	 */
	public function element(): ?ActorElementDefinition;

	/**
	 * Возвращает правила наложения runtime-эффектов поверхностью.
	 *
	 * @return list<SurfaceEffectRuleData>
	 */
	public function effectRules(): array;

	/**
	 * Возвращает теги поверхности.
	 *
	 * @return list<string>
	 */
	public function tags(): array;
}

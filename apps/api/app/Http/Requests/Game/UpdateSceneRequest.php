<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use App\Models\Actor;
use App\Support\SceneCatalog\SceneObjectCatalog;
use App\Support\SceneCatalog\SceneSurfaceCatalog;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует payload для полного сохранения authored-сцены.
 */
final class UpdateSceneRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации authored-сцены.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'min:2', 'max:120'],
			'description' => ['nullable', 'string', 'max:1000'],
			'width' => ['required', 'integer', 'min:6', 'max:64'],
			'height' => ['required', 'integer', 'min:6', 'max:64'],
			'metadata' => ['nullable', 'array'],
			'cells' => ['required', 'array', 'min:36'],
			'cells.*.x' => ['required', 'integer', 'min:0'],
			'cells.*.y' => ['required', 'integer', 'min:0'],
			'cells.*.terrain_type' => ['required', 'string', 'in:'.implode(',', SceneSurfaceCatalog::codes())],
			'cells.*.elevation' => ['nullable', 'integer', 'min:-20', 'max:20'],
			'cells.*.is_passable' => ['nullable', 'boolean'],
			'cells.*.blocks_vision' => ['nullable', 'boolean'],
			'cells.*.props' => ['nullable', 'array'],
			'objects' => ['nullable', 'array'],
			'objects.*.kind' => ['required', 'string', 'in:'.implode(',', SceneObjectCatalog::codes())],
			'objects.*.name' => ['nullable', 'string', 'max:120'],
			'objects.*.x' => ['required', 'integer', 'min:0'],
			'objects.*.y' => ['required', 'integer', 'min:0'],
			'objects.*.width' => ['nullable', 'integer', 'min:1', 'max:4'],
			'objects.*.height' => ['nullable', 'integer', 'min:1', 'max:4'],
			'objects.*.is_hidden' => ['nullable', 'boolean'],
			'objects.*.is_interactive' => ['nullable', 'boolean'],
			'objects.*.state' => ['nullable', 'array'],
			'actors' => ['nullable', 'array'],
			'actors.*.actor_id' => ['required', 'integer', 'distinct', 'exists:actors,id'],
			'actors.*.x' => ['required', 'integer', 'min:0'],
			'actors.*.y' => ['required', 'integer', 'min:0'],
		];
	}
}

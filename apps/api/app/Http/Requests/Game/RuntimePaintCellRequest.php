<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use App\Domain\Scene\SceneSurfaceCatalog;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует изменение поверхности клетки в runtime-сцене.
 */
final class RuntimePaintCellRequest extends FormRequest
{
	/**
	 * Разрешает выполнение запроса аутентифицированному мастеру.
	 */
	public function authorize(): bool
	{
		return $this->user('web') !== null;
	}

	/**
	 * Возвращает правила валидации runtime-покраски клетки.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'x' => ['required', 'integer', 'min:0'],
			'y' => ['required', 'integer', 'min:0'],
			'terrain_type' => ['required', 'string', 'in:'.implode(',', SceneSurfaceCatalog::codes())],
		];
	}
}

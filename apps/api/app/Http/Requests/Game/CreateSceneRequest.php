<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует payload для создания authored-сцены.
 */
final class CreateSceneRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации для создания authored-сцены.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'name' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
			'description' => ['nullable', 'string', 'max:1000'],
			'width' => ['nullable', 'integer', 'min:6', 'max:64'],
			'height' => ['nullable', 'integer', 'min:6', 'max:64'],
			'metadata' => ['nullable', 'array'],
		];
	}
}

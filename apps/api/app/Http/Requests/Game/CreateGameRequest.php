<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует payload для создания новой игры.
 */
final class CreateGameRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации для создания игры.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'title' => ['required', 'string', 'min:3', 'max:120'],
			'description' => ['nullable', 'string', 'max:1000'],
		];
	}
}

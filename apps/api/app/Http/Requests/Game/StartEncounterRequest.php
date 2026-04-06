<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует запуск encounter на активной runtime-сцене.
 */
final class StartEncounterRequest extends FormRequest
{
	/**
	 * Разрешает запрос аутентифицированному пользователю.
	 */
	public function authorize(): bool
	{
		return $this->user('web') !== null;
	}

	/**
	 * Возвращает правила выбора участников encounter.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'actor_ids' => ['nullable', 'array'],
			'actor_ids.*' => ['integer', 'distinct', 'min:1'],
		];
	}
}

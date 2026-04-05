<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует запрос на перемещение runtime-актора.
 */
final class MoveRuntimeActorRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации координат перемещения.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'x' => ['required', 'integer', 'min:0'],
			'y' => ['required', 'integer', 'min:0'],
		];
	}
}

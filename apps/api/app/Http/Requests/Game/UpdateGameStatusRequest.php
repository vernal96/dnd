<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидирует payload для смены статуса игры.
 */
final class UpdateGameStatusRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации для смены статуса игры.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'status' => ['required', 'string', Rule::in(['draft', 'active', 'paused', 'completed'])],
		];
	}
}

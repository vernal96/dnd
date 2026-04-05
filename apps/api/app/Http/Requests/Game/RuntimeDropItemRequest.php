<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует размещение предмета на активной runtime-сцене.
 */
final class RuntimeDropItemRequest extends FormRequest
{
	/**
	 * Разрешает выполнение запроса аутентифицированному мастеру.
	 */
	public function authorize(): bool
	{
		return $this->user('web') !== null;
	}

	/**
	 * Возвращает правила валидации дропа предмета.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'item_code' => ['required', 'string', 'min:1', 'max:64'],
			'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
			'x' => ['required', 'integer', 'min:0'],
			'y' => ['required', 'integer', 'min:0'],
		];
	}
}

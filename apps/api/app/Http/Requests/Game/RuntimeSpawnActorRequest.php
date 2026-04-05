<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидирует добавление runtime-актора на активную сцену.
 */
final class RuntimeSpawnActorRequest extends FormRequest
{
	/**
	 * Разрешает выполнение запроса аутентифицированному мастеру.
	 */
	public function authorize(): bool
	{
		return $this->user('web') !== null;
	}

	/**
	 * Возвращает правила валидации runtime-спауна актора.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'source_type' => ['required', 'string', Rule::in(['npc', 'player_character'])],
			'source_id' => ['required', 'integer', 'min:1'],
			'x' => ['required', 'integer', 'min:0'],
			'y' => ['required', 'integer', 'min:0'],
		];
	}
}

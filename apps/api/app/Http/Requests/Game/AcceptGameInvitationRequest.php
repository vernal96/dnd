<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует принятие приглашения игроком с выбором персонажа.
 */
final class AcceptGameInvitationRequest extends FormRequest
{
	/**
	 * Разрешает обработку запроса аутентифицированному игроку.
	 */
	public function authorize(): bool
	{
		return $this->user('web') !== null;
	}

	/**
	 * Возвращает правила валидации для принятия приглашения.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'character_id' => ['nullable', 'integer', 'min:1'],
		];
	}
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

/**
 * Валидирует payload для полного обновления persistent-актора игры.
 */
final class UpdateActorRequest extends CreateActorRequest
{
	/**
	 * Возвращает правила валидации для полного обновления актора.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		$rules['kind'][0] = 'required';
		$rules['level'][0] = 'required';
		$rules['movement_speed'][0] = 'required';

		return $rules;
	}
}

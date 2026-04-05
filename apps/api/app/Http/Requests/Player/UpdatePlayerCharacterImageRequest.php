<?php

declare(strict_types=1);

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Валидирует запрос на смену фото персонажа игрока.
 */
final class UpdatePlayerCharacterImageRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации пути изображения персонажа.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'image_path' => ['required', 'string', 'max:255'],
		];
	}

	/**
	 * Проверяет принадлежность изображения текущему игроку.
	 */
	public function withValidator(Validator $validator): void
	{
		$validator->after(function (Validator $validator): void {
			$payload = $validator->safe()->all();
			$imagePath = $payload['image_path'] ?? null;

			if (!is_string($imagePath) || $imagePath === '') {
				return;
			}

			$user = $this->user('web');

			if ($user === null) {
				return;
			}

			$expectedPrefix = 'player-characters/' . $user->id . '/';

			if (!str_starts_with(trim($imagePath, '/'), $expectedPrefix)) {
				$validator->errors()->add('image_path', 'Изображение должно принадлежать текущему игроку.');
			}
		});
	}
}

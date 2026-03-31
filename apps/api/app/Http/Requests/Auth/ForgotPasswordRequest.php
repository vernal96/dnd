<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует payload для запроса восстановления пароля.
 */
final class ForgotPasswordRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации для восстановления пароля.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'email' => ['required', 'email:rfc', 'max:255'],
		];
	}
}

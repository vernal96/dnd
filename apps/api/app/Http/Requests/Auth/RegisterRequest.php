<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Валидирует payload для регистрации пользователя.
 */
final class RegisterRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации для регистрации пользователя.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'login' => ['required', 'string', 'min:3', 'max:80', Rule::unique('users', 'name')],
			'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
			'password' => ['required', 'string', Password::min(8)],
		];
	}
}

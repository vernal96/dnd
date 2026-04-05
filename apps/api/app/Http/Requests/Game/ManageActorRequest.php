<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Базовый request для операций чтения и удаления акторов.
 */
final class ManageActorRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает пустой набор правил для route-only запроса.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [];
	}
}

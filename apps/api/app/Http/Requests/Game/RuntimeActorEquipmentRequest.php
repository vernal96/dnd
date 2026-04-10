<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use App\Domain\Actor\ActorEquipmentSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидирует intent-команду изменения экипировки runtime-актора.
 */
final class RuntimeActorEquipmentRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации экипировки.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'slot' => ['required', 'string', Rule::in(ActorEquipmentSlot::values())],
			'item_code' => ['nullable', 'string', 'max:64'],
		];
	}
}

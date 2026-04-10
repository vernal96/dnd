<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use App\Domain\Actor\ActorEquipmentSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидирует intent-команду runtime-действия актора.
 */
final class RuntimeActorActionRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации runtime-действия.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'action' => ['required', 'string', Rule::in(['weapon_attack', 'trip_attack'])],
			'target_actor_id' => ['required', 'integer', 'min:1'],
			'equipment_slot' => ['nullable', 'string', Rule::in(ActorEquipmentSlot::values())],
			'item_code' => ['nullable', 'string', 'max:64'],
		];
	}
}

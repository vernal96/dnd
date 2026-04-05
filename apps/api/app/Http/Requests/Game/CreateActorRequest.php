<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use App\Application\Catalog\ItemCatalog;
use App\Domain\Catalog\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Валидирует payload для создания persistent-актора игры.
 */
class CreateActorRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации для создания актора.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'kind' => ['nullable', 'string', Rule::in(['npc', 'player_character'])],
			'name' => ['required', 'string', 'min:2', 'max:120'],
			'description' => ['nullable', 'string', 'max:1000'],
			'race' => ['nullable', 'string', 'max:64'],
			'character_class' => ['nullable', 'string', 'max:64'],
			'level' => ['nullable', 'integer', 'min:1', 'max:20'],
			'movement_speed' => ['nullable', 'integer', 'min:1', 'max:20'],
			'base_health' => ['nullable', 'integer', 'min:1', 'max:9999'],
			'health_current' => ['nullable', 'integer', 'min:0'],
			'health_max' => ['nullable', 'integer', 'min:1'],
			'stats' => ['nullable', 'array'],
			'inventory' => ['nullable', 'array'],
			'inventory.*.item_code' => ['required', 'string', Rule::in($this->getActiveItemCodes())],
			'inventory.*.quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
			'inventory.*.slot' => ['nullable', 'string', 'max:64'],
			'inventory.*.is_equipped' => ['nullable', 'boolean'],
			'inventory.*.state' => ['nullable', 'array'],
			'image_path' => ['nullable', 'string', 'max:255'],
			'meta' => ['nullable', 'array'],
		];
	}

	/**
	 * Добавляет валидацию зависимых полей здоровья.
	 */
	public function withValidator(Validator $validator): void
	{
		$validator->after(function (Validator $validator): void {
			$healthCurrent = $this->input('health_current');
			$healthMax = $this->input('health_max');

			if ($this->filled('health_current') && !$this->filled('health_max')) {
				$validator->errors()->add('health_max', 'Максимальное здоровье обязательно, если задано текущее здоровье.');
			}

			if ($this->filled('base_health') && $this->filled('health_max') && (int) $this->input('base_health') !== (int) $healthMax) {
				$validator->errors()->add('base_health', 'Базовое здоровье должно совпадать с максимальным здоровьем, если оба поля заданы.');
			}

			if ($this->filled('health_current') && $this->filled('health_max') && $healthCurrent > $healthMax) {
				$validator->errors()->add('health_current', 'Текущее здоровье не может превышать максимальное.');
			}
		});
	}

	/**
	 * Возвращает список кодов активных предметов.
	 *
	 * @return list<string>
	 */
	private function getActiveItemCodes(): array
	{
		$itemCatalog = app(ItemCatalog::class);

		return array_map(
			static fn (Item $item): string => $item->getCode(),
			$itemCatalog->getActiveItems(),
		);
	}
}

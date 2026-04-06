<?php

declare(strict_types=1);

namespace App\Http\Requests\Player;

use App\Application\Catalog\AbilityCatalog;
use App\Application\Catalog\CharacterClassCatalog;
use App\Application\Catalog\RaceCatalog;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\Abilities\ConstitutionAbility;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Abilities\IntelligenceAbility;
use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\Abilities\WisdomAbility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Валидирует запрос на создание персонажа игрока.
 */
final class CreatePlayerCharacterRequest extends FormRequest
{
	/**
	 * Определяет, разрешено ли выполнение запроса.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Возвращает правила валидации персонажа игрока.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		$abilityCatalog = app(AbilityCatalog::class);

		return [
			'name' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
			'description' => ['nullable', 'string', 'max:1500'],
			'race' => ['sometimes', 'required', 'string', 'max:64'],
			'subrace' => ['nullable', 'string', 'max:64'],
			'character_class' => ['sometimes', 'required', 'string', 'max:64'],
			'image_path' => ['nullable', 'string', 'max:255'],
			'base_stats' => ['sometimes', 'required', 'array'],
			'base_stats.' . $abilityCatalog->getCodeByClass(StrengthAbility::class) => ['required_with:base_stats', 'integer', 'min:1', 'max:30'],
			'base_stats.' . $abilityCatalog->getCodeByClass(DexterityAbility::class) => ['required_with:base_stats', 'integer', 'min:1', 'max:30'],
			'base_stats.' . $abilityCatalog->getCodeByClass(ConstitutionAbility::class) => ['required_with:base_stats', 'integer', 'min:1', 'max:30'],
			'base_stats.' . $abilityCatalog->getCodeByClass(IntelligenceAbility::class) => ['required_with:base_stats', 'integer', 'min:1', 'max:30'],
			'base_stats.' . $abilityCatalog->getCodeByClass(WisdomAbility::class) => ['required_with:base_stats', 'integer', 'min:1', 'max:30'],
			'base_stats.' . $abilityCatalog->getCodeByClass(CharismaAbility::class) => ['required_with:base_stats', 'integer', 'min:1', 'max:30'],
		];
	}

	/**
	 * Добавляет доменные проверки кодов и бюджета характеристик.
	 */
	public function withValidator(Validator $validator): void
	{
		$validator->after(function (Validator $validator): void {
			$payload = $validator->safe()->all();

			if (!isset($payload['race']) || !is_string($payload['race'])) {
				return;
			}

			$raceCatalog = app(RaceCatalog::class);
			$race = $raceCatalog->findPlayerSelectableRaceByCode($payload['race']);

			if ($race === null) {
				$validator->errors()->add('race', 'Выбранная раса не существует.');
			}

			if (isset($payload['subrace']) && is_string($payload['subrace']) && trim($payload['subrace']) !== '') {
				$isKnownSubrace = false;

				if ($race !== null) {
					foreach ($race->getActiveSubraces() as $subrace) {
						if ($subrace->getCode() === $payload['subrace']) {
							$isKnownSubrace = true;
							break;
						}
					}
				}

				if (!$isKnownSubrace) {
					$validator->errors()->add('subrace', 'Выбранная подраса не принадлежит указанной расе.');
				}
			}

			$classBonuses = null;

			if (isset($payload['character_class']) && is_string($payload['character_class'])) {
				$classCatalog = app(CharacterClassCatalog::class);
				$characterClass = $classCatalog->findPlayerSelectableClassByCode($payload['character_class']);

				if ($characterClass === null) {
					$validator->errors()->add('character_class', 'Выбранный класс не существует.');
				} else {
					$classBonuses = $characterClass->getAbilityBonuses();
				}
			}

			if (isset($payload['base_stats']) && is_array($payload['base_stats'])) {
				$abilityCatalog = app(AbilityCatalog::class);
				$spentPoints = 0;
				$raceBonuses = $race?->getAbilityBonuses();
				$subraceBonuses = null;

				if ($race !== null && isset($payload['subrace']) && is_string($payload['subrace']) && trim($payload['subrace']) !== '') {
					foreach ($race->getActiveSubraces() as $subrace) {
						if ($subrace->getCode() === $payload['subrace']) {
							$subraceBonuses = $subrace->getAbilityBonuses();
							break;
						}
					}
				}

				foreach ($abilityCatalog->getAbilities() as $ability) {
					$code = $ability->getCode();
					$value = $payload['base_stats'][$code] ?? null;

					if (!is_int($value)) {
						$validator->errors()->add('base_stats.' . $code, 'Нужно указать значение характеристики.');
						continue;
					}

					$minimumValue = 1
						+ ($raceBonuses?->getByAbility($ability) ?? 0)
						+ ($subraceBonuses?->getByAbility($ability) ?? 0)
						+ ($classBonuses?->getByAbility($ability) ?? 0);

					if ($value < $minimumValue) {
						$validator->errors()->add('base_stats.' . $code, 'Значение характеристики ниже стартового минимума.');
						continue;
					}

					$spentPoints += $value - $minimumValue;
				}

				if ($spentPoints !== 27) {
					$validator->errors()->add('base_stats', 'Нужно распределить ровно 27 очков характеристик.');
				}
			}

			if (isset($payload['image_path']) && is_string($payload['image_path']) && $payload['image_path'] !== '') {
				$user = $this->user('web');

				if ($user !== null) {
					$expectedPrefix = 'player-characters/' . $user->id . '/';

					if (!str_starts_with(trim($payload['image_path'], '/'), $expectedPrefix)) {
						$validator->errors()->add('image_path', 'Изображение должно принадлежать текущему игроку.');
					}
				}
			}
		});
	}
}

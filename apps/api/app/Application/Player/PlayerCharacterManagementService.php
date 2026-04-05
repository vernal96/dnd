<?php

declare(strict_types=1);

namespace App\Application\Player;

use App\Application\Catalog\AbilityCatalog;
use App\Application\Catalog\CharacterClassCatalog;
use App\Application\Catalog\RaceCatalog;
use App\Data\Catalog\AbilityBonusesData;
use App\Data\Player\CreatePlayerCharacterData;
use App\Models\PlayerCharacter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Управляет persistent-персонажами текущего игрока.
 */
final class PlayerCharacterManagementService
{
	private const int POINT_BUY_POOL = 27;

	/**
	 * Создает сервис управления персонажами игрока.
	 */
	public function __construct(
		private readonly RaceCatalog $raceCatalog,
		private readonly CharacterClassCatalog $characterClassCatalog,
		private readonly AbilityCatalog $abilityCatalog,
	)
	{
	}

	/**
	 * Возвращает список персонажей текущего игрока.
	 *
	 * @return list<array<string, mixed>>
	 */
	public function getCharactersForPlayer(User $user): array
	{
		return PlayerCharacter::query()
			->where('user_id', $user->id)
			->orderByDesc('created_at')
			->orderByDesc('id')
			->get()
			->map(fn (PlayerCharacter $character): array => $this->buildCharacterPayload($character))
			->all();
	}

	/**
	 * Создает нового persistent-персонажа текущего игрока.
	 *
	 * @return array<string, mixed>
	 *
	 * @throws Throwable Если создание персонажа завершилось технической ошибкой.
	 */
	public function createCharacter(CreatePlayerCharacterData $data, User $user): array
	{
		$race = $this->raceCatalog->findActiveRaceByCode($data->raceCode);

		if ($race === null) {
			throw new RuntimeException('Раса персонажа не найдена.');
		}

		$subrace = null;

		if ($data->subraceCode !== null) {
			foreach ($race->getActiveSubraces() as $candidate) {
				if ($candidate->getCode() === $data->subraceCode) {
					$subrace = $candidate;
					break;
				}
			}

			if ($subrace === null) {
				throw new RuntimeException('Подраса не принадлежит выбранной расе.');
			}
		}

		$characterClass = $this->characterClassCatalog->findActiveClassByCode($data->classCode);

		if ($characterClass === null) {
			throw new RuntimeException('Класс персонажа не найден.');
		}

		$totalBonuses = $this->buildTotalBonuses(
			$race->toArray()['abilityBonuses'],
			$subrace?->toArray()['abilityBonuses'] ?? null,
			$characterClass->toArray()['abilityBonuses'],
		);

		$this->assertPointBuyBudget($data->baseStats, $totalBonuses);

		/** @var PlayerCharacter $character */
		$character = DB::transaction(function () use ($data, $user, $race, $subrace): PlayerCharacter {
			return PlayerCharacter::query()->create([
				'user_id' => $user->id,
				'name' => $data->name,
				'description' => $data->description,
				'race' => $data->raceCode,
				'subrace' => $data->subraceCode,
				'class' => $data->classCode,
				'level' => 1,
				'experience' => 0,
				'status' => 'active',
				'base_stats' => $data->baseStats,
				'derived_stats' => $data->baseStats,
				'image_path' => $data->imagePath,
				'meta' => null,
			]);
		});

		return $this->buildCharacterPayload($character);
	}

	/**
	 * Обновляет только фото существующего персонажа игрока.
	 *
	 * @return array<string, mixed>|null
	 *
	 * @throws Throwable Если обновление персонажа завершилось технической ошибкой.
	 */
	public function updateCharacterImage(int $characterId, string $imagePath, User $user): ?array
	{
		$character = PlayerCharacter::query()
			->where('id', $characterId)
			->where('user_id', $user->id)
			->first();

		if ($character === null) {
			return null;
		}

		DB::transaction(function () use ($character, $imagePath): void {
			$character->forceFill([
				'image_path' => trim($imagePath),
			])->save();
		});

		return $this->buildCharacterPayload($character->fresh() ?? $character);
	}

	/**
	 * Проверяет, что распределение очков характеристик укладывается в бюджет.
	 *
	 * @param array{str:int,dex:int,con:int,int:int,wis:int,cha:int} $baseStats
	 * @param array{str:int,dex:int,con:int,int:int,wis:int,cha:int} $totalBonuses
	 */
	private function assertPointBuyBudget(array $baseStats, array $totalBonuses): void
	{
		$spentPoints = 0;

		foreach ($this->abilityCatalog->getAbilities() as $ability) {
			$code = $ability->getCode();
			$value = $baseStats[$code] ?? 1;
			$minimumValue = 1 + ($totalBonuses[$code] ?? 0);

			if ($value < $minimumValue) {
				throw new RuntimeException('Значение характеристики не может быть меньше стартового значения.');
			}

			$spentPoints += $value - $minimumValue;
		}

		if ($spentPoints !== self::POINT_BUY_POOL) {
			throw new RuntimeException('Нужно распределить ровно 27 очков характеристик.');
		}
	}

	/**
	 * Собирает суммарные бонусы расы, подрасы и класса.
	 *
	 * @param array{str:int,dex:int,con:int,int:int,wis:int,cha:int} $raceBonuses
	 * @param array{str:int,dex:int,con:int,int:int,wis:int,cha:int}|null $subraceBonuses
	 * @param array{str:int,dex:int,con:int,int:int,wis:int,cha:int} $classBonuses
	 * @return array{str:int,dex:int,con:int,int:int,wis:int,cha:int}
	 */
	private function buildTotalBonuses(array $raceBonuses, ?array $subraceBonuses, array $classBonuses): array
	{
		$totalBonuses = (new AbilityBonusesData)->toArray();

		foreach ($this->abilityCatalog->getAbilities() as $ability) {
			$code = $ability->getCode();
			$totalBonuses[$code] += $raceBonuses[$code] ?? 0;
			$totalBonuses[$code] += $subraceBonuses[$code] ?? 0;
			$totalBonuses[$code] += $classBonuses[$code] ?? 0;
		}

		return $totalBonuses;
	}

	/**
	 * Преобразует модель персонажа в payload ответа API.
	 *
	 * @return array<string, mixed>
	 */
	private function buildCharacterPayload(PlayerCharacter $character): array
	{
		$race = is_string($character->race) && $character->race !== ''
			? $this->raceCatalog->findActiveRaceByCode($character->race)
			: null;
		$characterClass = is_string($character->class) && $character->class !== ''
			? $this->characterClassCatalog->findActiveClassByCode($character->class)
			: null;
		$subraceName = null;

		if ($race !== null && is_string($character->subrace) && $character->subrace !== '') {
			foreach ($race->getActiveSubraces() as $subrace) {
				if ($subrace->getCode() === $character->subrace) {
					$subraceName = $subrace->getName();
					break;
				}
			}
		}

		return [
			'id' => $character->id,
			'user_id' => $character->user_id,
			'name' => $character->name,
			'description' => $character->description,
			'race' => $character->race,
			'race_name' => $race?->getName(),
			'subrace' => $character->subrace,
			'subrace_name' => $subraceName,
			'character_class' => $character->class,
			'character_class_name' => $characterClass?->getName(),
			'level' => $character->level,
			'experience' => $character->experience,
			'status' => $character->status,
			'base_stats' => $character->base_stats,
			'derived_stats' => $character->derived_stats,
			'image_path' => $character->image_path,
			'image_url' => $character->image_url,
			'created_at' => $character->created_at?->toJSON(),
			'updated_at' => $character->updated_at?->toJSON(),
		];
	}
}

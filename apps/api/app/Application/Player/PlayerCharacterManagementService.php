<?php

declare(strict_types=1);

namespace App\Application\Player;

use App\Application\Catalog\AbilityCatalog;
use App\Application\Catalog\CharacterClassCatalog;
use App\Application\Catalog\RaceCatalog;
use App\Data\Catalog\AbilityBonusesData;
use App\Data\Player\CreatePlayerCharacterData;
use App\Data\Player\PlayerCharacterViewData;
use App\Domain\Actor\Abilities\CharismaAbility;
use App\Domain\Actor\Abilities\ConstitutionAbility;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Abilities\IntelligenceAbility;
use App\Domain\Actor\Abilities\StrengthAbility;
use App\Domain\Actor\Abilities\WisdomAbility;
use App\Domain\Actor\AbstractCharacterClass;
use App\Domain\Actor\AbstractRace;
use App\Models\GameMember;
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
	 * @return list<PlayerCharacterViewData>
	 */
	public function getCharactersForPlayer(User $user): array
	{
		return PlayerCharacter::query()
			->where('user_id', $user->id)
			->orderByDesc('created_at')
			->orderByDesc('id')
			->get()
			->map(fn (PlayerCharacter $character): PlayerCharacterViewData => $this->buildCharacterView($character))
			->all();
	}

	/**
	 * Возвращает только тех персонажей игрока, которые можно использовать для входа в указанную игру.
	 *
	 * @return list<PlayerCharacterViewData>
	 */
	public function getAvailableCharactersForGame(User $user, int $gameId): array
	{
		return PlayerCharacter::query()
			->where('user_id', $user->id)
			->orderByDesc('created_at')
			->orderByDesc('id')
			->get()
			->filter(fn (PlayerCharacter $character): bool => $this->findBlockingMembership($character, $gameId) === null)
			->map(fn (PlayerCharacter $character): PlayerCharacterViewData => $this->buildCharacterView($character))
			->values()
			->all();
	}

	/**
	 * Проверяет, может ли игрок использовать персонажа для входа в указанную игру.
	 */
	public function assertCharacterCanJoinGame(int $characterId, int $gameId, User $user): PlayerCharacter
	{
		$character = PlayerCharacter::query()
			->where('id', $characterId)
			->where('user_id', $user->id)
			->first();

		if ($character === null) {
			throw new RuntimeException('Выбранный персонаж не найден.');
		}

		$blockingMembership = $this->findBlockingMembership($character, $gameId);

		if ($blockingMembership !== null) {
			$gameTitle = $blockingMembership->game?->title ?? 'другой игре';
			throw new RuntimeException('Этот персонаж уже участвует в игре "' . $gameTitle . '".');
		}

		return $character;
	}

	/**
	 * Создает нового persistent-персонажа текущего игрока.
	 *
	 * @throws Throwable Если создание персонажа завершилось технической ошибкой.
	 */
	public function createCharacter(CreatePlayerCharacterData $data, User $user): PlayerCharacterViewData
	{
		$race = $this->raceCatalog->findPlayerSelectableRaceByCode($data->raceCode);

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

		$characterClass = $this->characterClassCatalog->findPlayerSelectableClassByCode($data->classCode);

		if ($characterClass === null) {
			throw new RuntimeException('Класс персонажа не найден.');
		}

		$totalBonuses = $this->buildTotalBonuses(
			$race->getAbilityBonuses(),
			$subrace?->getAbilityBonuses(),
			$characterClass->getAbilityBonuses(),
		);

		$this->assertPointBuyBudget($data->baseStats, $totalBonuses);

		$derivedStats = $this->buildDerivedStats($data->baseStats, $race, $characterClass);

		/** @var PlayerCharacter $character */
		$character = DB::transaction(function () use ($data, $user, $derivedStats): PlayerCharacter {
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
				'derived_stats' => $derivedStats,
				'image_path' => $data->imagePath,
				'meta' => null,
			]);
		});

		return $this->buildCharacterView($character);
	}

	/**
	 * Обновляет только фото существующего персонажа игрока.
	 *
	 * @throws Throwable Если обновление персонажа завершилось технической ошибкой.
	 */
	public function updateCharacterImage(int $characterId, string $imagePath, User $user): ?PlayerCharacterViewData
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

		return $this->buildCharacterView($character->fresh() ?? $character);
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
	 * @param AbilityBonusesData $raceBonuses
	 * @param AbilityBonusesData|null $subraceBonuses
	 * @param AbilityBonusesData $classBonuses
	 * @return array{str:int,dex:int,con:int,int:int,wis:int,cha:int}
	 */
	private function buildTotalBonuses(
		AbilityBonusesData $raceBonuses,
		?AbilityBonusesData $subraceBonuses,
		AbilityBonusesData $classBonuses,
	): array
	{
		$totalBonuses = [
			$this->abilityCatalog->getCodeByClass(StrengthAbility::class) => 0,
			$this->abilityCatalog->getCodeByClass(DexterityAbility::class) => 0,
			$this->abilityCatalog->getCodeByClass(ConstitutionAbility::class) => 0,
			$this->abilityCatalog->getCodeByClass(IntelligenceAbility::class) => 0,
			$this->abilityCatalog->getCodeByClass(WisdomAbility::class) => 0,
			$this->abilityCatalog->getCodeByClass(CharismaAbility::class) => 0,
		];

		foreach ($this->abilityCatalog->getAbilities() as $ability) {
			$code = $ability->getCode();
			$totalBonuses[$code] += $raceBonuses->getByAbility($ability);
			$totalBonuses[$code] += $subraceBonuses?->getByAbility($ability) ?? 0;
			$totalBonuses[$code] += $classBonuses->getByAbility($ability);
		}

		return $totalBonuses;
	}

	/**
	 * Собирает производные характеристики персонажа на основе базы, расы и класса.
	 *
	 * @param array{str:int,dex:int,con:int,int:int,wis:int,cha:int} $baseStats
	 * @return array<string, int>
	 */
	private function buildDerivedStats(
		array $baseStats,
		AbstractRace $race,
		AbstractCharacterClass $characterClass,
	): array
	{
		return [
			...$baseStats,
			'speed' => 6 + $race->getSpeedBonus() + $characterClass->getSpeedBonus(),
			'health' => $race->getHealthBonus() + $characterClass->getHealthBonus(),
		];
	}

	/**
	 * Преобразует модель персонажа в типизированное представление ответа API.
	 */
	private function buildCharacterView(PlayerCharacter $character): PlayerCharacterViewData
	{
		$activeMembership = GameMember::query()
			->where('player_character_id', $character->id)
			->where('status', 'active')
			->whereHas('game', static function ($query): void {
				$query->where('status', '!=', 'completed');
			})
			->with('game:id,title,status')
			->first();
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

		return new PlayerCharacterViewData(
			id: $character->id,
			userId: $character->user_id,
			name: $character->name,
			description: $character->description,
			race: $character->race,
			raceName: $race?->getName(),
			subrace: $character->subrace,
			subraceName: $subraceName,
			characterClass: $character->class,
			characterClassName: $characterClass?->getName(),
			level: $character->level,
			experience: $character->experience,
			status: $character->status,
			baseStats: $character->base_stats,
			derivedStats: $character->derived_stats,
			imagePath: $character->image_path,
			imageUrl: $character->image_url,
			activeGameId: $activeMembership?->game_id,
			activeGameTitle: $activeMembership?->game?->title,
			isAvailableForJoin: $activeMembership === null,
			createdAt: $character->created_at?->toJSON(),
			updatedAt: $character->updated_at?->toJSON(),
		);
	}

	/**
	 * Возвращает активное участие персонажа в другой незавершенной игре, если оно есть.
	 */
	private function findBlockingMembership(PlayerCharacter $character, int $gameId): ?GameMember
	{
		return GameMember::query()
			->where('player_character_id', $character->id)
			->where('status', 'active')
			->where('game_id', '!=', $gameId)
			->whereHas('game', static function ($query): void {
				$query->where('status', '!=', 'completed');
			})
			->with('game:id,title,status')
			->first();
	}
}

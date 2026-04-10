<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Application\Catalog\ItemCatalog;
use App\Application\Catalog\ItemCatalogImageStorageService;
use App\Application\Realtime\RealtimePublisher;
use App\Data\Game\RuntimeEncounterData;
use App\Data\Game\RuntimeEncounterParticipantData;
use App\Data\Game\RuntimeItemDropData;
use App\Data\Game\RuntimeSceneViewData;
use App\Domain\Actor\Abilities\DexterityAbility;
use App\Domain\Actor\Ability;
use App\Domain\Actor\ActorEquipmentSlot;
use App\Domain\Actor\ActorEffect;
use App\Domain\Actor\Dice;
use App\Domain\Actor\Item;
use App\Domain\Actor\ItemType;
use App\Domain\Actor\LuckScale;
use App\Domain\Scene\SceneSurfaceCatalog;
use App\Domain\Scene\Surfaces\SceneSurfaceDefinition;
use App\Models\Actor;
use App\Models\ActorInstance;
use App\Models\Encounter;
use App\Models\EncounterParticipant;
use App\Models\Game;
use App\Models\GameMember;
use App\Models\GameSceneState;
use App\Models\PlayerCharacter;
use App\Models\SceneTemplateCell;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Управляет runtime-сценой игры мастера: активацией authored-сцены и изменениями в живой игре.
 */
final class RuntimeSceneManagementService
{
	/**
	 * Создает сервис runtime-сцены.
	 */
	public function __construct(
		private readonly ItemCatalog $itemCatalog,
		private readonly ItemCatalogImageStorageService $itemCatalogImageStorageService,
		private readonly ActorCombatStatsService $actorCombatStatsService,
		private readonly ActorEquipmentService $actorEquipmentService,
		private readonly SurfaceEffectService $surfaceEffectService,
		private readonly RandomDiceRollerService $diceRoller,
		private readonly RealtimePublisher $realtimePublisher,
	)
	{
	}

	/**
	 * Возвращает активную runtime-сцену игры текущего мастера.
	 */
	public function findActiveSceneForGameMaster(int $gameId, User $user): ?GameSceneState
	{
		$game = $this->findOwnedGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		$sceneState = $this->resolveActiveSceneState($game);

		if ($sceneState === null) {
			return null;
		}

		return $this->loadRuntimeScene($sceneState);
	}

	/**
	 * Возвращает активную runtime-сцену игры, в которой участвует текущий игрок.
	 */
	public function findActiveSceneForPlayer(int $gameId, User $user): ?GameSceneState
	{
		$game = $this->findPlayableGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		$sceneState = $this->resolveActiveSceneState($game);

		if ($sceneState === null) {
			return null;
		}

		return $this->loadRuntimeScene($sceneState);
	}

	/**
	 * Подготавливает runtime-сцену для HTTP-ответа.
	 */
	public function buildSceneView(GameSceneState $sceneState): RuntimeSceneViewData
	{
		$sceneState = $this->loadRuntimeScene($sceneState);

		return new RuntimeSceneViewData(
			sceneState: $sceneState,
			itemDrops: $this->normalizeItemDrops($sceneState),
			encounter: $this->buildActiveEncounterPayload($sceneState),
		);
	}

	/**
	 * Запускает боевой режим на активной runtime-сцене.
	 *
	 * @param list<int> $actorIds
	 * @throws RuntimeException Если не удалось сформировать encounter.
	 * @throws Throwable Если запуск encounter завершился технической ошибкой.
	 */
	public function startEncounter(int $gameId, array $actorIds, User $user): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$runtimeActors = ActorInstance::query()
			->where('game_scene_state_id', $sceneState->id)
			->whereNotNull('x')
			->whereNotNull('y')
			->get();

		if ($runtimeActors->isEmpty()) {
			throw new RuntimeException('На активной сцене нет актеров для запуска сражения.');
		}

		$selectedActors = $actorIds === []
			? $runtimeActors->values()
			: $runtimeActors
				->whereIn('id', $actorIds)
				->values();

		if ($selectedActors->isEmpty()) {
			throw new RuntimeException('Не выбраны участники сражения.');
		}

		$encounter = DB::transaction(function () use ($game, $sceneState, $selectedActors): Encounter {
			Encounter::query()
				->where('game_scene_state_id', $sceneState->id)
				->where('status', 'active')
				->update([
					'status' => 'resolved',
					'resolved_at' => now(),
					'current_participant_id' => null,
				]);

			/** @var Encounter $encounter */
			$encounter = Encounter::query()->create([
				'game_id' => $game->id,
				'game_scene_state_id' => $sceneState->id,
				'status' => 'active',
				'round' => 1,
				'trigger_type' => 'manual',
				'payload' => [
					'selected_actor_ids' => $selectedActors->pluck('id')->values()->all(),
				],
				'started_at' => now(),
			]);

			$initiativeRows = $selectedActors
				->map(function (ActorInstance $actor): array {
					$dexterity = $actor->runtime_state['stats'][(new DexterityAbility)->getCode()] ?? null;
					$dexterityValue = is_int($dexterity) ? $dexterity : 10;
					$dexterityModifier = (int) floor(($dexterityValue - 10) / 2);

					return [
						'actor' => $actor,
						'dexterity_modifier' => $dexterityModifier,
						'initiative' => random_int(1, 20) + $dexterityModifier,
					];
				})
				->sort(static function (array $left, array $right): int {
					if ($left['initiative'] !== $right['initiative']) {
						return $right['initiative'] <=> $left['initiative'];
					}

					if ($left['dexterity_modifier'] !== $right['dexterity_modifier']) {
						return $right['dexterity_modifier'] <=> $left['dexterity_modifier'];
					}

					/** @var ActorInstance $leftActor */
					$leftActor = $left['actor'];
					/** @var ActorInstance $rightActor */
					$rightActor = $right['actor'];

					return $leftActor->id <=> $rightActor->id;
				})
				->values();

			$currentParticipantId = null;

			foreach ($initiativeRows as $index => $row) {
				/** @var ActorInstance $actor */
				$actor = $row['actor'];

				/** @var EncounterParticipant $participant */
				$participant = EncounterParticipant::query()->create([
					'encounter_id' => $encounter->id,
					'actor_id' => $actor->id,
					'initiative' => $row['initiative'],
					'turn_order' => $index + 1,
					'joined_round' => 1,
					'is_hidden' => false,
					'is_surprised' => false,
					'movement_left' => $this->surfaceEffectService->resolveEffectiveMovementSpeed($actor),
					'action_available' => true,
					'bonus_action_available' => true,
					'reaction_available' => true,
					'combat_result_state' => 'active',
				]);

				if ($index === 0) {
					$currentParticipantId = $participant->id;
				}
			}

			$encounter->forceFill([
				'current_participant_id' => $currentParticipantId,
			])->save();

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();

			return $encounter;
		});

		$sceneState->refresh();
		$encounter->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Активирует authored-сцену как текущую runtime-сцену игры и пересоздает runtime-акторов.
	 *
	 * @throws Throwable Если активация сцены завершилась технической ошибкой.
	 */
	public function activateScene(int $gameId, int $sceneStateId, User $user): ?GameSceneState
	{
		$game = $this->findOwnedGame($gameId, $user);
		$sceneState = $this->findOwnedSceneState($gameId, $sceneStateId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		DB::transaction(function () use ($game, $sceneState): void {
			$sceneState->loadMissing([
				'sceneTemplate.actorPlacements.actor',
				'sceneTemplate.cells',
			]);
			$game->loadMissing([
				'members.playerCharacter',
			]);

			ActorInstance::query()
				->where('game_scene_state_id', $sceneState->id)
				->delete();

			Encounter::query()
				->where('game_scene_state_id', $sceneState->id)
				->where('status', 'active')
				->update([
					'status' => 'resolved',
					'resolved_at' => now(),
					'current_participant_id' => null,
				]);

			$occupiedCells = [];

			foreach ($sceneState->sceneTemplate->actorPlacements as $placement) {
				$actor = $placement->actor;

				if (!$actor instanceof Actor) {
					continue;
				}

				ActorInstance::query()->create([
					'game_id' => $game->id,
					'game_scene_state_id' => $sceneState->id,
					'controlled_by_user_id' => null,
					'kind' => $actor->kind,
					'controller_type' => $actor->kind === 'npc' ? 'gm' : 'player',
					'name' => $actor->name,
					'status' => 'active',
					'x' => $placement->x,
					'y' => $placement->y,
					'hp_current' => $this->resolveActorHitPoints($actor),
					'hp_max' => $this->resolveActorHitPoints($actor),
					'luck' => $actor->luck,
					'is_hidden' => false,
					'resources' => null,
					'temporary_effects' => null,
					'runtime_state' => [
						'source_actor_id' => $actor->id,
						'image_path' => $actor->image_path,
						'image_url' => $actor->image_url,
						'race' => $actor->race,
						'character_class' => $actor->character_class,
						'level' => $actor->level,
						...$this->buildActorRuntimeDerivedStats($actor),
						'luck' => $actor->luck,
						'stats' => $actor->stats,
						'inventory' => $actor->inventory,
					],
				]);

				$occupiedCells[$placement->x.':'.$placement->y] = true;
			}

			$spawnCells = $this->resolvePlayerSpawnCells($sceneState, $game, $occupiedCells);

			foreach ($game->members as $member) {
				if ($member->role !== 'player' || $member->status !== 'active') {
					continue;
				}

				$playerCharacter = $member->playerCharacter;

				if (!$playerCharacter instanceof PlayerCharacter) {
					continue;
				}

				$spawnCell = array_shift($spawnCells);

				if ($spawnCell === null) {
					continue;
				}

				$derivedStats = $this->buildPlayerRuntimeDerivedStats($playerCharacter);
				$hitPoints = $derivedStats['health'];

				ActorInstance::query()->create([
					'game_id' => $game->id,
					'game_scene_state_id' => $sceneState->id,
					'player_character_id' => $playerCharacter->id,
					'controlled_by_user_id' => $member->user_id,
					'kind' => 'player_character',
					'controller_type' => 'player',
					'name' => $playerCharacter->name,
					'status' => 'active',
					'x' => $spawnCell['x'],
					'y' => $spawnCell['y'],
					'hp_current' => $hitPoints,
					'hp_max' => $hitPoints,
					'luck' => $playerCharacter->luck,
					'is_hidden' => false,
					'resources' => null,
					'temporary_effects' => null,
					'runtime_state' => [
						'source_actor_id' => null,
						'image_path' => $playerCharacter->image_path,
						'image_url' => $playerCharacter->image_url,
						'race' => $playerCharacter->race,
						'character_class' => $playerCharacter->class,
						'level' => $playerCharacter->level,
						...$derivedStats,
						'luck' => $playerCharacter->luck,
						'stats' => $playerCharacter->base_stats,
						'inventory' => [],
					],
				]);
			}

			$game->forceFill([
				'active_scene_state_id' => $sceneState->id,
			])->save();

			$sceneState->forceFill([
				'status' => 'active',
				'loaded_at' => now(),
				'resolved_at' => null,
				'version' => $sceneState->version + 1,
				'grid_state' => null,
				'runtime_state' => [
					'activated_at' => now()?->toAtomString(),
					'item_drops' => [],
				],
			])->save();
		});

		try {
			$this->realtimePublisher->publishGameSceneActivated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Завершает активный encounter на runtime-сцене по инициативе мастера.
	 *
	 * @throws Throwable Если завершение encounter завершилось технической ошибкой.
	 */
	public function endEncounter(int $gameId, User $user): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		DB::transaction(function () use ($sceneState): void {
			Encounter::query()
				->where('game_scene_state_id', $sceneState->id)
				->where('status', 'active')
				->update([
					'status' => 'resolved',
					'resolved_at' => now(),
					'current_participant_id' => null,
				]);

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();
		});

		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Перемещает runtime-актора мастером в новую клетку активной сцены.
	 *
	 * @throws RuntimeException Если перемещение нарушает ограничения runtime-сцены.
	 * @throws Throwable Если перемещение завершилось технической ошибкой.
	 */
	public function moveActor(int $gameId, int $actorInstanceId, int $x, int $y, User $user): ?ActorInstance
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		/** @var ActorInstance|null $actorInstance */
		$actorInstance = ActorInstance::query()
			->where('id', $actorInstanceId)
			->where('game_id', $game->id)
			->where('game_scene_state_id', $sceneState->id)
			->first();

		if ($actorInstance === null) {
			return null;
		}

		$this->assertCellInsideScene($sceneState, $x, $y, 'Нельзя переместить актора за пределы активной сцены.');

		$isOccupied = ActorInstance::query()
			->where('game_scene_state_id', $sceneState->id)
			->where('x', $x)
			->where('y', $y)
			->where('id', '!=', $actorInstance->id)
			->exists();

		if ($isOccupied) {
			throw new RuntimeException('Целевая клетка уже занята другим персонажем.');
		}

		if ($this->surfaceEffectService->hasActiveEffect($actorInstance, ActorEffect::Prone)) {
			throw new RuntimeException('Актор упал и не может двигаться.');
		}

		$activeEncounter = $this->findActiveEncounter($sceneState);

		if ($activeEncounter instanceof Encounter) {
			$participant = $this->findEncounterParticipant($activeEncounter, $actorInstance->id);

			if (!$participant instanceof EncounterParticipant || $activeEncounter->current_participant_id !== $participant->id) {
				throw new RuntimeException('Сейчас не ход этого участника.');
			}

			if ($actorInstance->x === null || $actorInstance->y === null) {
				throw new RuntimeException('Актор не размещен на активной сцене.');
			}

			$distance = abs($actorInstance->x - $x) + abs($actorInstance->y - $y);

			if ($distance > (int) $participant->movement_left) {
				throw new RuntimeException('Для такого перемещения не хватает оставшейся скорости.');
			}
		}

		$previousX = $actorInstance->x;
		$previousY = $actorInstance->y;
		$movementDistance = $previousX !== null && $previousY !== null
			? abs($previousX - $x) + abs($previousY - $y)
			: 0;

		DB::transaction(function () use ($actorInstance, $sceneState, $x, $y, $activeEncounter, $movementDistance): void {
			$actorInstance->forceFill([
				'x' => $x,
				'y' => $y,
			])->save();

			$surface = $this->resolveSurfaceAtCoordinates($sceneState, $x, $y);

			if ($surface instanceof SceneSurfaceDefinition) {
				$this->surfaceEffectService->applySurfaceEffect($actorInstance, $surface);
			}

			if ($activeEncounter instanceof Encounter && $movementDistance > 0) {
				$participant = $this->findEncounterParticipant($activeEncounter, $actorInstance->id);

				if ($participant instanceof EncounterParticipant) {
					$participant->forceFill([
						'movement_left' => max(0, (int) $participant->movement_left - $movementDistance),
					])->save();
				}
			}

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();
		});

		$actorInstance->refresh();
		$sceneState->refresh();

		try {
			if ($activeEncounter instanceof Encounter) {
				$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
			} else {
				$this->realtimePublisher->publishRuntimeActorMoved($game, $sceneState, $actorInstance);
			}
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $actorInstance;
	}

	/**
	 * Перемещает runtime-актора, контролируемого текущим игроком.
	 *
	 * @throws RuntimeException Если перемещение нарушает правила player-runtime.
	 * @throws Throwable Если перемещение завершилось технической ошибкой.
	 */
	public function moveActorForPlayer(int $gameId, int $actorInstanceId, int $x, int $y, User $user): ?ActorInstance
	{
		$game = $this->findPlayableGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		$sceneState = $this->resolveActiveSceneState($game);

		if ($sceneState === null) {
			return null;
		}

		/** @var ActorInstance|null $actorInstance */
		$actorInstance = ActorInstance::query()
			->where('id', $actorInstanceId)
			->where('game_id', $game->id)
			->where('game_scene_state_id', $sceneState->id)
			->where('controlled_by_user_id', $user->id)
			->where('controller_type', 'player')
			->first();

		if ($actorInstance === null) {
			return null;
		}

		$this->assertCellInsideScene($sceneState, $x, $y, 'Нельзя переместить героя за пределы активной сцены.');

		$isOccupied = ActorInstance::query()
			->where('game_scene_state_id', $sceneState->id)
			->where('x', $x)
			->where('y', $y)
			->where('id', '!=', $actorInstance->id)
			->exists();

		if ($isOccupied) {
			throw new RuntimeException('Целевая клетка уже занята другим персонажем.');
		}

		if ($actorInstance->x === null || $actorInstance->y === null) {
			throw new RuntimeException('Герой не размещен на активной сцене.');
		}

		if ($this->surfaceEffectService->hasActiveEffect($actorInstance, ActorEffect::Prone)) {
			throw new RuntimeException('Герой упал и не может двигаться.');
		}

		$movementSpeed = $this->surfaceEffectService->resolveEffectiveMovementSpeed($actorInstance);
		$distance = abs($actorInstance->x - $x) + abs($actorInstance->y - $y);
		$activeEncounter = $this->findActiveEncounter($sceneState);

		if ($activeEncounter instanceof Encounter) {
			$participant = $this->findEncounterParticipant($activeEncounter, $actorInstance->id);

			if (!$participant instanceof EncounterParticipant || $activeEncounter->current_participant_id !== $participant->id) {
				throw new RuntimeException('Сейчас не твой ход.');
			}

			if ($distance > (int) $participant->movement_left) {
				throw new RuntimeException('Для такого перемещения не хватает оставшейся скорости.');
			}
		}

		if (!$activeEncounter instanceof Encounter && $movementSpeed > 0 && $distance > $movementSpeed) {
			throw new RuntimeException('Целевая клетка находится слишком далеко для текущего перемещения.');
		}

		DB::transaction(function () use ($actorInstance, $sceneState, $x, $y, $activeEncounter, $distance): void {
			$actorInstance->forceFill([
				'x' => $x,
				'y' => $y,
			])->save();

			$surface = $this->resolveSurfaceAtCoordinates($sceneState, $x, $y);

			if ($surface instanceof SceneSurfaceDefinition) {
				$this->surfaceEffectService->applySurfaceEffect($actorInstance, $surface);
			}

			if ($activeEncounter instanceof Encounter && $distance > 0) {
				$participant = $this->findEncounterParticipant($activeEncounter, $actorInstance->id);

				if ($participant instanceof EncounterParticipant) {
					$participant->forceFill([
						'movement_left' => max(0, (int) $participant->movement_left - $distance),
					])->save();
				}
			}

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();
		});

		$actorInstance->refresh();
		$sceneState->refresh();

		try {
			if ($activeEncounter instanceof Encounter) {
				$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
			} else {
				$this->realtimePublisher->publishRuntimeActorMoved($game, $sceneState, $actorInstance);
			}
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $actorInstance;
	}

	/**
	 * Выполняет runtime-действие актора от имени мастера.
	 *
	 * @throws RuntimeException Если действие нарушает правила runtime-сцены.
	 * @throws Throwable Если выполнение действия завершилось технической ошибкой.
	 */
	public function performRuntimeAction(
		int $gameId,
		int $actorInstanceId,
		string $action,
		int $targetActorId,
		?string $equipmentSlot,
		?string $itemCode,
		User $user,
	): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$actorInstance = $this->findRuntimeActorInScene($sceneState, $actorInstanceId);

		if (!$actorInstance instanceof ActorInstance) {
			return null;
		}

		$this->performRuntimeActionInternal($game, $sceneState, $actorInstance, $action, $targetActorId, $equipmentSlot, $itemCode, null);

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Выполняет runtime-действие героя игрока.
	 *
	 * @throws RuntimeException Если действие нарушает правила runtime-сцены.
	 * @throws Throwable Если выполнение действия завершилось технической ошибкой.
	 */
	public function performRuntimeActionForPlayer(
		int $gameId,
		int $actorInstanceId,
		string $action,
		int $targetActorId,
		?string $equipmentSlot,
		?string $itemCode,
		User $user,
	): ?GameSceneState
	{
		$game = $this->findPlayableGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		$sceneState = $this->resolveActiveSceneState($game);

		if ($sceneState === null) {
			return null;
		}

		$actorInstance = ActorInstance::query()
			->where('id', $actorInstanceId)
			->where('game_id', $game->id)
			->where('game_scene_state_id', $sceneState->id)
			->where('controlled_by_user_id', $user->id)
			->where('controller_type', 'player')
			->first();

		if (!$actorInstance instanceof ActorInstance) {
			return null;
		}

		$this->performRuntimeActionInternal($game, $sceneState, $actorInstance, $action, $targetActorId, $equipmentSlot, $itemCode, $user->id);

		return $this->findActiveSceneForPlayer($gameId, $user);
	}

	/**
	 * Экипирует предмет runtime-актору от имени мастера.
	 *
	 * @throws RuntimeException Если экипировка нарушает правила runtime-сцены.
	 * @throws Throwable Если изменение экипировки завершилось технической ошибкой.
	 */
	public function equipRuntimeActor(
		int $gameId,
		int $actorInstanceId,
		string $slot,
		?string $itemCode,
		User $user,
	): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$actorInstance = $this->findRuntimeActorInScene($sceneState, $actorInstanceId);

		if (!$actorInstance instanceof ActorInstance) {
			return null;
		}

		$this->equipRuntimeActorInternal($game, $sceneState, $actorInstance, $slot, $itemCode);

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Экипирует предмет герою текущего игрока.
	 *
	 * @throws RuntimeException Если экипировка нарушает правила runtime-сцены.
	 * @throws Throwable Если изменение экипировки завершилось технической ошибкой.
	 */
	public function equipRuntimeActorForPlayer(
		int $gameId,
		int $actorInstanceId,
		string $slot,
		?string $itemCode,
		User $user,
	): ?GameSceneState
	{
		$game = $this->findPlayableGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		$sceneState = $this->resolveActiveSceneState($game);

		if ($sceneState === null) {
			return null;
		}

		$actorInstance = ActorInstance::query()
			->where('id', $actorInstanceId)
			->where('game_id', $game->id)
			->where('game_scene_state_id', $sceneState->id)
			->where('controlled_by_user_id', $user->id)
			->where('controller_type', 'player')
			->first();

		if (!$actorInstance instanceof ActorInstance) {
			return null;
		}

		$this->equipRuntimeActorInternal($game, $sceneState, $actorInstance, $slot, $itemCode);

		return $this->findActiveSceneForPlayer($gameId, $user);
	}

	/**
	 * Добавляет нового runtime-актора на активную сцену.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	public function spawnActor(int $gameId, string $sourceType, int $sourceId, int $x, int $y, User $user): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$this->assertCellIsFreeForRuntime($sceneState, $x, $y);

		$createdActorInstance = DB::transaction(function () use ($game, $sceneState, $sourceType, $sourceId, $x, $y): ActorInstance {
			if ($sourceType === 'npc') {
				$actor = Actor::query()
					->where('id', $sourceId)
					->where('gm_user_id', $game->gm_user_id)
					->first();

				if (!$actor instanceof Actor) {
					throw new RuntimeException('NPC для размещения не найден.');
				}

				/** @var ActorInstance $actorInstance */
				$actorInstance = ActorInstance::query()->create([
					'game_id' => $game->id,
					'game_scene_state_id' => $sceneState->id,
					'controlled_by_user_id' => null,
					'kind' => 'npc',
					'controller_type' => 'gm',
					'name' => $actor->name,
					'status' => 'active',
					'x' => $x,
					'y' => $y,
					'hp_current' => $this->resolveActorHitPoints($actor),
					'hp_max' => $this->resolveActorHitPoints($actor),
					'luck' => $actor->luck,
					'is_hidden' => false,
					'resources' => null,
					'temporary_effects' => null,
					'runtime_state' => [
						'source_actor_id' => $actor->id,
						'image_path' => $actor->image_path,
						'image_url' => $actor->image_url,
						'race' => $actor->race,
						'character_class' => $actor->character_class,
						'level' => $actor->level,
						...$this->buildActorRuntimeDerivedStats($actor),
						'luck' => $actor->luck,
						'stats' => $actor->stats,
						'inventory' => $actor->inventory,
					],
				]);

				$sceneState->forceFill([
					'version' => $sceneState->version + 1,
				])->save();

				return $actorInstance;
			}

			if ($sourceType === 'player_character') {
				$member = GameMember::query()
					->where('game_id', $game->id)
					->where('player_character_id', $sourceId)
					->where('role', 'player')
					->where('status', 'active')
					->with('playerCharacter')
					->first();

				if (!$member instanceof GameMember || !$member->playerCharacter instanceof PlayerCharacter) {
					throw new RuntimeException('Герой игрока для размещения не найден в этом столе.');
				}

				$alreadySpawned = ActorInstance::query()
					->where('game_scene_state_id', $sceneState->id)
					->where('player_character_id', $sourceId)
					->exists();

				if ($alreadySpawned) {
					throw new RuntimeException('Этот герой уже размещен на активной сцене.');
				}

				$character = $member->playerCharacter;
				$derivedStats = $this->buildPlayerRuntimeDerivedStats($character);
				$hitPoints = $derivedStats['health'];

				/** @var ActorInstance $actorInstance */
				$actorInstance = ActorInstance::query()->create([
					'game_id' => $game->id,
					'game_scene_state_id' => $sceneState->id,
					'player_character_id' => $character->id,
					'controlled_by_user_id' => $member->user_id,
					'kind' => 'player_character',
					'controller_type' => 'player',
					'name' => $character->name,
					'status' => 'active',
					'x' => $x,
					'y' => $y,
					'hp_current' => $hitPoints,
					'hp_max' => $hitPoints,
					'luck' => $character->luck,
					'is_hidden' => false,
					'resources' => null,
					'temporary_effects' => null,
					'runtime_state' => [
						'source_actor_id' => null,
						'image_path' => $character->image_path,
						'image_url' => $character->image_url,
						'race' => $character->race,
						'character_class' => $character->class,
						'level' => $character->level,
						...$derivedStats,
						'luck' => $character->luck,
						'stats' => $character->base_stats,
						'inventory' => [],
					],
				]);

				$sceneState->forceFill([
					'version' => $sceneState->version + 1,
				])->save();

				return $actorInstance;
			}

			throw new RuntimeException('Неизвестный тип runtime-актора.');
		});

		$createdActorInstance->refresh();
		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishRuntimeActorSpawned($game, $sceneState, $createdActorInstance);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Меняет поверхность клетки на активной runtime-сцене.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	public function paintCell(int $gameId, int $x, int $y, string $terrainType, User $user): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$this->assertCellInsideScene($sceneState, $x, $y, 'Нельзя изменить поверхность за пределами активной сцены.');

		$surface = SceneSurfaceCatalog::resolve($terrainType);

		if ($surface === null) {
			throw new RuntimeException('Поверхность не найдена.');
		}

		DB::transaction(function () use ($sceneState, $x, $y, $surface): void {
			$gridState = is_array($sceneState->grid_state) ? $sceneState->grid_state : [];
			$cellOverrides = is_array($gridState['cells'] ?? null) ? $gridState['cells'] : [];
			$updated = false;

			foreach ($cellOverrides as $index => $override) {
				if (($override['x'] ?? null) === $x && ($override['y'] ?? null) === $y) {
					$cellOverrides[$index] = [
						'x' => $x,
						'y' => $y,
						'terrain_type' => (string) ($surface['code'] ?? 'grass'),
						'is_passable' => (bool) ($surface['is_passable'] ?? true),
						'blocks_vision' => (bool) ($surface['blocks_vision'] ?? false),
					];
					$updated = true;
					break;
				}
			}

			if (!$updated) {
				$cellOverrides[] = [
					'x' => $x,
					'y' => $y,
					'terrain_type' => (string) ($surface['code'] ?? 'grass'),
					'is_passable' => (bool) ($surface['is_passable'] ?? true),
					'blocks_vision' => (bool) ($surface['blocks_vision'] ?? false),
				];
			}

			$sceneState->forceFill([
				'grid_state' => [
					'cells' => array_values($cellOverrides),
				],
				'version' => $sceneState->version + 1,
			])->save();
		});

		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishRuntimeCellPainted(
				$game,
				$sceneState,
				$x,
				$y,
				(string) ($surface['code'] ?? 'grass'),
				(bool) ($surface['is_passable'] ?? true),
				(bool) ($surface['blocks_vision'] ?? false),
			);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Добавляет дроп предмета на активную runtime-сцену.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	public function dropItem(int $gameId, string $itemCode, int $x, int $y, int $quantity, User $user): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$this->assertCellInsideScene($sceneState, $x, $y, 'Нельзя разместить предмет за пределами активной сцены.');

		$item = $this->itemCatalog->findActiveItemByCode($itemCode);

		if ($item === null) {
			throw new RuntimeException('Предмет не найден в каталоге.');
		}

		$itemDrop = DB::transaction(function () use ($sceneState, $item, $x, $y, $quantity): array {
			$runtimeState = is_array($sceneState->runtime_state) ? $sceneState->runtime_state : [];
			$itemDrops = is_array($runtimeState['item_drops'] ?? null) ? $runtimeState['item_drops'] : [];
			$itemDrop = [
				'id' => (string) Str::uuid(),
				'item_code' => $item->getCode(),
				'name' => $item->getName(),
				'quantity' => max(1, $quantity),
				'x' => $x,
				'y' => $y,
			];
			$itemDrops[] = $itemDrop;
			$runtimeState['item_drops'] = array_values($itemDrops);

			$sceneState->forceFill([
				'runtime_state' => $runtimeState,
				'version' => $sceneState->version + 1,
			])->save();

			return $itemDrop;
		});

		$sceneState->refresh();
		$normalizedItemDrop = $this->normalizeItemDrop($sceneState, $itemDrop);

		try {
			$this->realtimePublisher->publishRuntimeItemDropped($game, $sceneState, $normalizedItemDrop);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Расходует основное или дополнительное действие текущего участника encounter от имени мастера.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	public function useEncounterAction(int $gameId, int $actorInstanceId, string $actionType, User $user): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$this->consumeEncounterAction($sceneState, $actorInstanceId, $actionType, null);
		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Расходует действие текущего участника encounter от имени игрока.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	public function useEncounterActionForPlayer(int $gameId, int $actorInstanceId, string $actionType, User $user): ?GameSceneState
	{
		$game = $this->findPlayableGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		$sceneState = $this->resolveActiveSceneState($game);

		if ($sceneState === null) {
			return null;
		}

		$this->consumeEncounterAction($sceneState, $actorInstanceId, $actionType, $user->id);
		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForPlayer($gameId, $user);
	}

	/**
	 * Завершает текущий ход encounter от имени мастера.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	public function advanceEncounterTurn(int $gameId, User $user): ?GameSceneState
	{
		[$game, $sceneState] = $this->resolveOwnedActiveScene($gameId, $user);

		if ($game === null || $sceneState === null) {
			return null;
		}

		$this->advanceEncounter($sceneState, null);
		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForGameMaster($gameId, $user);
	}

	/**
	 * Завершает текущий ход encounter от имени игрока.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	public function advanceEncounterTurnForPlayer(int $gameId, int $actorInstanceId, User $user): ?GameSceneState
	{
		$game = $this->findPlayableGame($gameId, $user);

		if ($game === null) {
			return null;
		}

		$sceneState = $this->resolveActiveSceneState($game);

		if ($sceneState === null) {
			return null;
		}

		$this->advanceEncounter($sceneState, [
			'actor_id' => $actorInstanceId,
			'user_id' => $user->id,
		]);
		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}

		return $this->findActiveSceneForPlayer($gameId, $user);
	}

	/**
	 * Возвращает игру, принадлежащую текущему мастеру.
	 */
	private function findOwnedGame(int $gameId, User $user): ?Game
	{
		return Game::query()
			->where('id', $gameId)
			->where('gm_user_id', $user->id)
			->first();
	}

	/**
	 * Возвращает runtime-состояние сцены, принадлежащее игре текущего мастера.
	 */
	private function findOwnedSceneState(int $gameId, int $sceneStateId, User $user): ?GameSceneState
	{
		return GameSceneState::query()
			->where('id', $sceneStateId)
			->where('game_id', $gameId)
			->whereHas('game', static function ($query) use ($user): void {
				$query->where('gm_user_id', $user->id);
			})
			->first();
	}

	/**
	 * Возвращает игру, в которой текущий пользователь участвует как активный игрок.
	 */
	private function findPlayableGame(int $gameId, User $user): ?Game
	{
		return Game::query()
			->where('id', $gameId)
			->whereHas('members', static function ($query) use ($user): void {
				$query
					->where('user_id', $user->id)
					->where('role', 'player')
					->where('status', 'active')
					->whereNotNull('player_character_id');
			})
			->first();
	}

	/**
	 * Загружает полное runtime-состояние сцены для frontend мастера.
	 */
	private function loadRuntimeScene(GameSceneState $sceneState): GameSceneState
	{
		$sceneState->load([
			'game:id,title,status,gm_user_id,active_scene_state_id',
			'sceneTemplate:id,created_by,name,description,width,height,status,metadata,created_at,updated_at',
			'sceneTemplate.cells',
			'sceneTemplate.objects',
			'actorInstances',
		]);

		$gridState = is_array($sceneState->grid_state) ? $sceneState->grid_state : [];
		$cellOverrides = is_array($gridState['cells'] ?? null) ? $gridState['cells'] : [];

		if ($cellOverrides !== []) {
			$overrideMap = [];

			foreach ($cellOverrides as $override) {
				if (!is_array($override)) {
					continue;
				}

				$overrideMap[($override['x'] ?? '').':'.($override['y'] ?? '')] = $override;
			}

			foreach ($sceneState->sceneTemplate->cells as $cell) {
				$key = $cell->x.':'.$cell->y;
				$override = $overrideMap[$key] ?? null;

				if (!is_array($override)) {
					continue;
				}

				$cell->terrain_type = is_string($override['terrain_type'] ?? null) ? $override['terrain_type'] : $cell->terrain_type;
				$cell->is_passable = is_bool($override['is_passable'] ?? null) ? $override['is_passable'] : $cell->is_passable;
				$cell->blocks_vision = is_bool($override['blocks_vision'] ?? null) ? $override['blocks_vision'] : $cell->blocks_vision;
			}
		}

		return $sceneState;
	}

	/**
	 * Нормализует runtime-дропы предметов для API.
	 *
	 * @return list<RuntimeItemDropData>
	 */
	private function normalizeItemDrops(GameSceneState $sceneState): array
	{
		return array_values(array_map(
			fn (array $itemDrop): RuntimeItemDropData => $this->normalizeItemDrop($sceneState, $itemDrop),
			$sceneState->item_drops,
		));
	}

	/**
	 * Дополняет runtime-дроп предмета ссылкой на изображение.
	 *
	 * @param array<string, mixed> $itemDrop
	 */
	private function normalizeItemDrop(GameSceneState $sceneState, array $itemDrop): RuntimeItemDropData
	{
		$itemCode = is_string($itemDrop['item_code'] ?? null) ? $itemDrop['item_code'] : null;
		$imageUrl = null;

		if ($itemCode !== null) {
			$item = $this->itemCatalog->findActiveItemByCode($itemCode);
			$imageUrl = $item?->image() !== null
				? $this->itemCatalogImageStorageService->buildImageUrl($item->image())
				: null;
		}

		return new RuntimeItemDropData(
			id: (string) ($itemDrop['id'] ?? ''),
			itemCode: $itemCode ?? '',
			name: is_string($itemDrop['name'] ?? null) ? $itemDrop['name'] : null,
			quantity: max(1, (int) ($itemDrop['quantity'] ?? 1)),
			x: (int) ($itemDrop['x'] ?? 0),
			y: (int) ($itemDrop['y'] ?? 0),
			imageUrl: $imageUrl,
		);
	}

	/**
	 * Возвращает кодовую поверхность клетки активной сцены.
	 */
	private function resolveSurfaceAtCoordinates(GameSceneState $sceneState, int $x, int $y): ?SceneSurfaceDefinition
	{
		$sceneState->loadMissing('sceneTemplate.cells');
		$terrainType = null;
		$gridState = is_array($sceneState->grid_state) ? $sceneState->grid_state : [];
		$cellOverrides = is_array($gridState['cells'] ?? null) ? $gridState['cells'] : [];

		foreach ($cellOverrides as $override) {
			if (!is_array($override)) {
				continue;
			}

			if (($override['x'] ?? null) === $x && ($override['y'] ?? null) === $y) {
				$terrainType = is_string($override['terrain_type'] ?? null) ? $override['terrain_type'] : null;
				break;
			}
		}

		if ($terrainType === null) {
			/** @var SceneTemplateCell|null $cell */
			$cell = $sceneState->sceneTemplate->cells
				->first(static fn ($cell): bool => $cell->x === $x && $cell->y === $y);

			$terrainType = $cell?->terrain_type;
		}

		return is_string($terrainType)
			? SceneSurfaceCatalog::findDefinition($terrainType)
			: null;
	}

	/**
	 * Возвращает игру и активную сцену текущего мастера.
	 *
	 * @return array{0:?Game,1:?GameSceneState}
	 */
	private function resolveOwnedActiveScene(int $gameId, User $user): array
	{
		$game = $this->findOwnedGame($gameId, $user);

		if ($game === null) {
			return [null, null];
		}

		return [$game, $this->resolveActiveSceneState($game)];
	}

	/**
	 * Возвращает активный encounter для runtime-сцены.
	 */
	private function findActiveEncounter(GameSceneState $sceneState): ?Encounter
	{
		return Encounter::query()
			->where('game_scene_state_id', $sceneState->id)
			->where('status', 'active')
			->with(['participants.actor', 'currentParticipant'])
			->orderByDesc('id')
			->first();
	}

	/**
	 * Возвращает encounter participant по actor id.
	 */
	private function findEncounterParticipant(Encounter $encounter, int $actorInstanceId): ?EncounterParticipant
	{
		return $encounter->participants
			->first(static fn (EncounterParticipant $participant): bool => $participant->actor_id === $actorInstanceId);
	}

	/**
	 * Преобразует активный encounter в payload frontend.
	 */
	private function buildActiveEncounterPayload(GameSceneState $sceneState): ?RuntimeEncounterData
	{
		$encounter = $this->findActiveEncounter($sceneState);

		if (!$encounter instanceof Encounter) {
			return null;
		}

		return new RuntimeEncounterData(
			id: $encounter->id,
			status: $encounter->status,
			round: $encounter->round,
			currentParticipantId: $encounter->current_participant_id,
			startedAt: $encounter->started_at?->toAtomString(),
			participants: $encounter->participants
				->sortBy('turn_order')
				->values()
				->map(static fn (EncounterParticipant $participant): RuntimeEncounterParticipantData => new RuntimeEncounterParticipantData(
					id: $participant->id,
					actorId: $participant->actor_id,
					initiative: $participant->initiative,
					turnOrder: $participant->turn_order,
					joinedRound: $participant->joined_round,
					movementLeft: $participant->movement_left,
					actionAvailable: (bool) $participant->action_available,
					bonusActionAvailable: (bool) $participant->bonus_action_available,
					reactionAvailable: (bool) $participant->reaction_available,
					combatResultState: $participant->combat_result_state,
					actor: $participant->actor,
				))
				->all(),
		);
	}

	/**
	 * Расходует действие участника encounter.
	 *
	 * @param int|null $requiredUserId
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	private function consumeEncounterAction(GameSceneState $sceneState, int $actorInstanceId, string $actionType, ?int $requiredUserId): void
	{
		$encounter = $this->findActiveEncounter($sceneState);

		if (!$encounter instanceof Encounter) {
			throw new RuntimeException('Сражение на этой сцене не запущено.');
		}

		$participant = $this->findEncounterParticipant($encounter, $actorInstanceId);

		if (!$participant instanceof EncounterParticipant || $encounter->current_participant_id !== $participant->id) {
			throw new RuntimeException('Сейчас не ход этого участника.');
		}

		if ($requiredUserId !== null) {
			$actor = $participant->actor;

			if (!$actor instanceof ActorInstance || $actor->controlled_by_user_id !== $requiredUserId) {
				throw new RuntimeException('Нельзя управлять действием чужого персонажа.');
			}
		}

		$column = match ($actionType) {
			'action' => 'action_available',
			'bonus-action' => 'bonus_action_available',
			default => throw new RuntimeException('Неизвестный тип боевого действия.'),
		};

		if (!$participant->{$column}) {
			throw new RuntimeException($actionType === 'action'
				? 'Основное действие уже израсходовано.'
				: 'Дополнительное действие уже израсходовано.');
		}

		DB::transaction(function () use ($participant, $sceneState, $column): void {
			$participant->forceFill([
				$column => false,
			])->save();

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();
		});
	}

	/**
	 * Применяет изменение экипировки runtime-актора и публикует обновление сцены.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	private function equipRuntimeActorInternal(
		Game $game,
		GameSceneState $sceneState,
		ActorInstance $actorInstance,
		string $slot,
		?string $itemCode,
	): void
	{
		$equipmentSlot = ActorEquipmentSlot::tryFrom($slot);

		if (!$equipmentSlot instanceof ActorEquipmentSlot) {
			throw new RuntimeException('Неизвестный слот экипировки.');
		}

		DB::transaction(function () use ($sceneState, $actorInstance, $equipmentSlot, $itemCode): void {
			$this->actorEquipmentService->equipRuntimeActor($actorInstance, $equipmentSlot, $itemCode);
			$runtimeState = is_array($actorInstance->runtime_state) ? $actorInstance->runtime_state : [];
			$runtimeState['armor_class'] = $this->resolveRuntimeActorArmorClass($actorInstance);

			$actorInstance->forceFill([
				'runtime_state' => $runtimeState,
			])->save();

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();
		});

		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}
	}

	/**
	 * Выполняет runtime-действие и фиксирует результат на сцене.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	private function performRuntimeActionInternal(
		Game $game,
		GameSceneState $sceneState,
		ActorInstance $actorInstance,
		string $action,
		int $targetActorId,
		?string $equipmentSlot,
		?string $itemCode,
		?int $requiredUserId,
	): void
	{
		$targetActor = $this->findRuntimeActorInScene($sceneState, $targetActorId);

		if (!$targetActor instanceof ActorInstance) {
			throw new RuntimeException('Цель атаки не найдена на активной сцене.');
		}

		if ($targetActor->id === $actorInstance->id) {
			throw new RuntimeException('Нельзя атаковать самого себя.');
		}

		if ($actorInstance->x === null || $actorInstance->y === null || $targetActor->x === null || $targetActor->y === null) {
			throw new RuntimeException('Атакующий и цель должны быть размещены на сцене.');
		}

		$activeEncounter = $this->findActiveEncounter($sceneState);
		$participant = null;

		if ($activeEncounter instanceof Encounter) {
			$participant = $this->findEncounterParticipant($activeEncounter, $actorInstance->id);

			if (!$participant instanceof EncounterParticipant || $activeEncounter->current_participant_id !== $participant->id) {
				throw new RuntimeException('Сейчас не ход этого участника.');
			}

			if ($requiredUserId !== null && $actorInstance->controlled_by_user_id !== $requiredUserId) {
				throw new RuntimeException('Нельзя выполнить действие чужим персонажем.');
			}

			if (!$participant->action_available) {
				throw new RuntimeException('Основное действие уже израсходовано.');
			}
		}

		match ($action) {
			'weapon_attack' => $this->performWeaponAttack($game, $sceneState, $actorInstance, $targetActor, $participant, $equipmentSlot, $itemCode),
			'trip_attack' => $this->performTripAttack($game, $sceneState, $actorInstance, $targetActor, $participant),
			default => throw new RuntimeException('Неизвестное runtime-действие.'),
		};
	}

	/**
	 * Выполняет атаку оружием.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	private function performWeaponAttack(
		Game $game,
		GameSceneState $sceneState,
		ActorInstance $actorInstance,
		ActorInstance $targetActor,
		?EncounterParticipant $participant,
		?string $equipmentSlot,
		?string $itemCode,
	): void
	{
		$item = $this->resolveWeaponForActor($actorInstance, $equipmentSlot, $itemCode);
		$damageDice = $item->getDamageDice();

		if ($damageDice === null) {
			throw new RuntimeException('Выбранный предмет не может наносить урон.');
		}

		$distance = abs($actorInstance->x - $targetActor->x) + abs($actorInstance->y - $targetActor->y);

		if ($item->getType() === ItemType::MeleeWeapon && $distance > 1) {
			throw new RuntimeException('Цель слишком далеко для ближней атаки.');
		}

		$primaryAbility = $this->resolvePrimaryAttackAbility($item);
		$stats = is_array($actorInstance->runtime_state['stats'] ?? null) ? $actorInstance->runtime_state['stats'] : [];
		$level = (int) ($actorInstance->runtime_state['level'] ?? 1);
		$primaryModifier = $this->actorCombatStatsService->resolveAbilityModifier($this->resolveRuntimeAbilityValue($stats, $primaryAbility));
		$proficiencyBonus = (int) floor((max(1, $level) - 1) / 4);
		$luckScale = LuckScale::tryFrom($actorInstance->luck) ?? LuckScale::Normal;
		$attackRoll = $this->diceRoller->roll(Dice::D20, $luckScale);
		$attackTotal = $attackRoll + $primaryModifier + $proficiencyBonus;
		$targetArmorClass = (int) ($targetActor->runtime_state['armor_class'] ?? 10);
		$isHit = $attackRoll === 20 || ($attackRoll !== 1 && $attackTotal >= $targetArmorClass);
		$damageRoll = $isHit ? $this->diceRoller->roll($damageDice, $luckScale) : 0;
		$damageBonus = $isHit ? $this->actorCombatStatsService->resolveDamageBonus($primaryModifier, $level) : 0;
		$damage = $isHit ? max(0, $damageRoll + $damageBonus) : 0;
		$nextHp = is_int($targetActor->hp_current) ? max(0, $targetActor->hp_current - $damage) : null;
		$actionLogEntry = [
			'id' => (string) Str::uuid(),
			'type' => 'weapon_attack',
			'actor_id' => $actorInstance->id,
			'actor_name' => $actorInstance->name,
			'target_actor_id' => $targetActor->id,
			'target_actor_name' => $targetActor->name,
			'item_code' => $item->getCode(),
			'item_name' => $item->getName(),
			'ability_code' => $primaryAbility->getCode(),
			'attack_roll' => $attackRoll,
			'attack_total' => $attackTotal,
			'target_armor_class' => $targetArmorClass,
			'is_hit' => $isHit,
			'damage_dice' => $damageDice->value,
			'damage_roll' => $damageRoll,
			'damage_bonus' => $damageBonus,
			'damage' => $damage,
			'created_at' => now()->toJSON(),
		];

		DB::transaction(function () use ($sceneState, $targetActor, $nextHp, $participant, $actionLogEntry): void {
			if ($nextHp !== null) {
				$targetActor->forceFill([
					'hp_current' => $nextHp,
				])->save();
			}

			if ($participant instanceof EncounterParticipant) {
				$participant->forceFill([
					'action_available' => false,
				])->save();
			}

			$runtimeState = is_array($sceneState->runtime_state) ? $sceneState->runtime_state : [];
			$actionLog = is_array($runtimeState['action_log'] ?? null) ? $runtimeState['action_log'] : [];
			$actionLog[] = $actionLogEntry;
			$runtimeState['action_log'] = array_slice($actionLog, -50);

			$sceneState->forceFill([
				'runtime_state' => $runtimeState,
				'version' => $sceneState->version + 1,
			])->save();
		});

		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}
	}

	/**
	 * Выполняет прием сбивания с ног через спасбросок Силы цели.
	 *
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	private function performTripAttack(
		Game $game,
		GameSceneState $sceneState,
		ActorInstance $actorInstance,
		ActorInstance $targetActor,
		?EncounterParticipant $participant,
	): void
	{
		$distance = abs((int) $actorInstance->x - (int) $targetActor->x) + abs((int) $actorInstance->y - (int) $targetActor->y);

		if ($distance > 1) {
			throw new RuntimeException('Цель слишком далеко, чтобы сбить ее с ног.');
		}

		$attackerStats = is_array($actorInstance->runtime_state['stats'] ?? null) ? $actorInstance->runtime_state['stats'] : [];
		$targetStats = is_array($targetActor->runtime_state['stats'] ?? null) ? $targetActor->runtime_state['stats'] : [];
		$attackerStrengthModifier = $this->actorCombatStatsService->resolveAbilityModifier($this->resolveRuntimeAbilityValueByCode($attackerStats, 'str'));
		$targetStrengthModifier = $this->actorCombatStatsService->resolveAbilityModifier($this->resolveRuntimeAbilityValueByCode($targetStats, 'str'));
		$attackerLevel = (int) ($actorInstance->runtime_state['level'] ?? 1);
		$difficultyClass = 10 + $attackerStrengthModifier + (int) floor((max(1, $attackerLevel) - 1) / 4);
		$luckScale = LuckScale::tryFrom($targetActor->luck) ?? LuckScale::Normal;
		$savingThrowRoll = $this->diceRoller->roll(Dice::D20, $luckScale);
		$savingThrowTotal = $savingThrowRoll + $targetStrengthModifier;
		$isFailed = $savingThrowRoll === 1 || ($savingThrowRoll !== 20 && $savingThrowTotal < $difficultyClass);
		$actionLogEntry = [
			'id' => (string) Str::uuid(),
			'type' => 'trip_attack',
			'actor_id' => $actorInstance->id,
			'actor_name' => $actorInstance->name,
			'target_actor_id' => $targetActor->id,
			'target_actor_name' => $targetActor->name,
			'save_ability_code' => 'str',
			'save_roll' => $savingThrowRoll,
			'save_total' => $savingThrowTotal,
			'difficulty_class' => $difficultyClass,
			'is_failed' => $isFailed,
			'applied_effect_code' => $isFailed ? ActorEffect::Prone->value : null,
			'created_at' => now()->toJSON(),
		];

		DB::transaction(function () use ($sceneState, $targetActor, $participant, $actionLogEntry, $isFailed): void {
			if ($isFailed) {
				$this->applyTemporaryEffect(
					actorInstance: $targetActor,
					effect: ActorEffect::Prone,
					sourceType: 'runtime_action',
					sourceCode: 'trip_attack',
					durationTurns: 1,
					durationSeconds: 10,
				);
			}

			if ($participant instanceof EncounterParticipant) {
				$participant->forceFill([
					'action_available' => false,
				])->save();
			}

			$runtimeState = is_array($sceneState->runtime_state) ? $sceneState->runtime_state : [];
			$actionLog = is_array($runtimeState['action_log'] ?? null) ? $runtimeState['action_log'] : [];
			$actionLog[] = $actionLogEntry;
			$runtimeState['action_log'] = array_slice($actionLog, -50);

			$sceneState->forceFill([
				'runtime_state' => $runtimeState,
				'version' => $sceneState->version + 1,
			])->save();
		});

		$sceneState->refresh();

		try {
			$this->realtimePublisher->publishGameSceneUpdated($game, $sceneState);
		} catch (Throwable $throwable) {
			report($throwable);
		}
	}

	/**
	 * Переводит encounter на следующий ход.
	 *
	 * @param array{actor_id:int,user_id:int}|null $playerGuard
	 * @throws RuntimeException
	 * @throws Throwable
	 */
	private function advanceEncounter(GameSceneState $sceneState, ?array $playerGuard): void
	{
		$encounter = $this->findActiveEncounter($sceneState);

		if (!$encounter instanceof Encounter) {
			throw new RuntimeException('Сражение на этой сцене не запущено.');
		}

		$participants = $encounter->participants
			->sortBy('turn_order')
			->values();

		$currentIndex = $participants->search(
			static fn (EncounterParticipant $participant): bool => $participant->id === $encounter->current_participant_id,
		);

		if (!is_int($currentIndex) || !isset($participants[$currentIndex])) {
			throw new RuntimeException('Не удалось определить текущего участника сражения.');
		}

		$currentParticipant = $participants[$currentIndex];

		if ($playerGuard !== null) {
			$currentActor = $currentParticipant->actor;

			if (
				!$currentActor instanceof ActorInstance
				|| $currentActor->id !== $playerGuard['actor_id']
				|| $currentActor->controlled_by_user_id !== $playerGuard['user_id']
			) {
				throw new RuntimeException('Нельзя завершить чужой ход.');
			}
		}

		/** @var list<EncounterParticipant> $orderedParticipants */
		$orderedParticipants = $participants->all();
		$nextTurn = $this->resolveNextEncounterTurn($orderedParticipants, $currentIndex, $encounter->round);
		$nextParticipant = $nextTurn['participant'];
		$nextMovementSpeed = $nextTurn['movement_speed'];
		$nextRound = $nextTurn['round'];

		DB::transaction(function () use ($encounter, $nextParticipant, $nextMovementSpeed, $nextRound, $sceneState): void {
			$nextParticipant->forceFill([
				'movement_left' => $nextMovementSpeed,
				'action_available' => true,
				'bonus_action_available' => true,
				'reaction_available' => true,
			])->save();

			$encounter->forceFill([
				'current_participant_id' => $nextParticipant->id,
				'round' => $nextRound,
			])->save();

			$sceneState->forceFill([
				'version' => $sceneState->version + 1,
			])->save();
		});
	}

	/**
	 * Возвращает следующего участника, пропуская ход акторов с эффектом падения.
	 *
	 * @param list<EncounterParticipant> $participants
	 * @return array{participant:EncounterParticipant,movement_speed:int,round:int}
	 */
	private function resolveNextEncounterTurn(array $participants, int $currentIndex, int $currentRound): array
	{
		$participantCount = count($participants);
		$nextIndex = $currentIndex;
		$nextRound = $currentRound;

		for ($attempt = 0; $attempt < max(1, $participantCount * 2); $attempt++) {
			$nextIndex++;

			if (!isset($participants[$nextIndex])) {
				$nextIndex = 0;
				$nextRound++;
			}

			$participant = $participants[$nextIndex];
			$actor = $participant->actor;

			if (!$actor instanceof ActorInstance) {
				return [
					'participant' => $participant,
					'movement_speed' => 0,
					'round' => $nextRound,
				];
			}

			if ($this->surfaceEffectService->consumeSkippedTurnIfNeeded($actor)) {
				$participant->forceFill([
					'movement_left' => 0,
					'action_available' => false,
					'bonus_action_available' => false,
					'reaction_available' => false,
				])->save();

				continue;
			}

			return [
				'participant' => $participant,
				'movement_speed' => $this->surfaceEffectService->resolveEffectiveMovementSpeed($actor),
				'round' => $nextRound,
			];
		}

		/** @var EncounterParticipant $fallbackParticipant */
		$fallbackParticipant = $participants[$nextIndex] ?? $participants[0];

		return [
			'participant' => $fallbackParticipant,
			'movement_speed' => 0,
			'round' => $nextRound,
		];
	}

	/**
	 * Возвращает runtime-актора из указанной сцены.
	 */
	private function findRuntimeActorInScene(GameSceneState $sceneState, int $actorInstanceId): ?ActorInstance
	{
		return ActorInstance::query()
			->where('id', $actorInstanceId)
			->where('game_scene_state_id', $sceneState->id)
			->where('game_id', $sceneState->game_id)
			->first();
	}

	/**
	 * Возвращает оружие для атаки актора.
	 */
	private function resolveWeaponForActor(ActorInstance $actorInstance, ?string $equipmentSlot, ?string $itemCode): Item
	{
		if (is_string($equipmentSlot) && $equipmentSlot !== '') {
			$slot = ActorEquipmentSlot::tryFrom($equipmentSlot);

			if (!$slot instanceof ActorEquipmentSlot) {
				throw new RuntimeException('Неизвестный слот экипировки.');
			}

			$item = $this->actorEquipmentService->resolveEquippedItem($actorInstance, $slot);

			if (!$item instanceof Item) {
				throw new RuntimeException('В выбранном слоте нет экипированного оружия.');
			}

			if (!$this->actorEquipmentService->isWeapon($item)) {
				throw new RuntimeException('Предмет в выбранном слоте не является оружием.');
			}

			return $item;
		}

		if (is_string($itemCode) && $itemCode !== '') {
			$item = $this->itemCatalog->findActiveItemByCode($itemCode);

			if (!$item instanceof Item) {
				throw new RuntimeException('Выбранное оружие не найдено.');
			}

			if (!in_array($item->getType(), [ItemType::MeleeWeapon, ItemType::RangedWeapon], true)) {
				throw new RuntimeException('Выбранный предмет не является оружием.');
			}

			return $item;
		}

		$equippedWeapon = $this->actorEquipmentService->resolveFirstEquippedWeapon($actorInstance);

		if ($equippedWeapon instanceof Item) {
			return $equippedWeapon;
		}

		$fallbackWeapon = $this->itemCatalog->findActiveItemByCode('longsword');

		if (!$fallbackWeapon instanceof Item) {
			throw new RuntimeException('Оружие по умолчанию не найдено.');
		}

		return $fallbackWeapon;
	}

	/**
	 * Возвращает основную характеристику атаки оружием.
	 */
	private function resolvePrimaryAttackAbility(Item $item): Ability
	{
		$ability = $item->getAttackAbilities()[0] ?? null;

		if (!$ability instanceof Ability) {
			throw new RuntimeException('Для оружия не задана основная характеристика атаки.');
		}

		return $ability;
	}

	/**
	 * Рассчитывает runtime AC с учетом экипированной брони.
	 */
	private function resolveRuntimeActorArmorClass(ActorInstance $actorInstance): int
	{
		$runtimeState = is_array($actorInstance->runtime_state) ? $actorInstance->runtime_state : [];
		$stats = is_array($runtimeState['stats'] ?? null) ? $runtimeState['stats'] : [];
		$level = (int) ($runtimeState['level'] ?? 1);
		$armor = $this->actorEquipmentService->resolveEquippedItem($actorInstance, ActorEquipmentSlot::Armor);

		if (!$armor instanceof Item) {
			return $this->actorCombatStatsService->buildDerivedStats($stats, $level)['armor_class'];
		}

		$armorClass = $armor->getArmorClassBase() ?? $this->actorCombatStatsService->baseArmorClass();
		$armorClass += $armor->getArmorClassBonus() ?? 0;
		$armorAbility = $armor->getArmorClassAbility();

		if ($armorAbility instanceof Ability) {
			$abilityModifier = $this->actorCombatStatsService->resolveAbilityModifier($this->resolveRuntimeAbilityValue($stats, $armorAbility));
			$abilityCap = $armor->getArmorClassAbilityCap();
			$armorClass += $abilityCap === null ? $abilityModifier : min($abilityModifier, $abilityCap);
		}

		return max(1, $armorClass);
	}

	/**
	 * Накладывает временный runtime-эффект на актора.
	 */
	private function applyTemporaryEffect(
		ActorInstance $actorInstance,
		ActorEffect $effect,
		string $sourceType,
		string $sourceCode,
		int $durationTurns,
		int $durationSeconds,
	): void
	{
		$effects = is_array($actorInstance->temporary_effects) ? $actorInstance->temporary_effects : [];
		$payload = [
			'code' => $effect->value,
			'label' => $effect->label(),
			'type' => $effect->type(),
			'icon' => $effect->icon(),
			'source_type' => $sourceType,
			'source_code' => $sourceCode,
			'remaining_turns' => max(0, $durationTurns),
			'expires_at' => now()->addSeconds(max(1, $durationSeconds))->toJSON(),
		];
		$wasUpdated = false;

		foreach ($effects as $index => $activeEffect) {
			if (!is_array($activeEffect) || ($activeEffect['code'] ?? null) !== $effect->value) {
				continue;
			}

			$effects[$index] = $payload;
			$wasUpdated = true;
			break;
		}

		if (!$wasUpdated) {
			$effects[] = $payload;
		}

		$actorInstance->forceFill([
			'temporary_effects' => array_values($effects),
		])->save();
	}

	/**
	 * Возвращает значение характеристики из runtime-статов актора.
	 *
	 * @param array<string, mixed> $stats
	 */
	private function resolveRuntimeAbilityValue(array $stats, Ability $ability): int
	{
		return $this->resolveRuntimeAbilityValueByCode($stats, $ability->getCode());
	}

	/**
	 * Возвращает значение характеристики из runtime-статов актора по коду.
	 *
	 * @param array<string, mixed> $stats
	 */
	private function resolveRuntimeAbilityValueByCode(array $stats, string $abilityCode): int
	{
		$value = $stats[$abilityCode] ?? null;

		if (is_int($value)) {
			return $value;
		}

		$legacyValue = $stats[$this->resolveLegacyAbilityCode($abilityCode)] ?? null;

		return is_int($legacyValue) ? $legacyValue : 10;
	}

	/**
	 * Возвращает legacy-код характеристики из ранних payload NPC.
	 */
	private function resolveLegacyAbilityCode(string $abilityCode): string
	{
		return match ($abilityCode) {
			'str' => 'strength',
			'dex' => 'dexterity',
			'con' => 'constitution',
			'int' => 'intelligence',
			'wis' => 'wisdom',
			'cha' => 'charisma',
			default => $abilityCode,
		};
	}

	/**
	 * Возвращает актуальную активную сцену игры и восстанавливает broken pointer при необходимости.
	 */
	private function resolveActiveSceneState(Game $game): ?GameSceneState
	{
		$sceneState = null;

		if ($game->active_scene_state_id !== null) {
			$sceneState = GameSceneState::query()
				->where('id', $game->active_scene_state_id)
				->where('game_id', $game->id)
				->first();
		}

		if (!$sceneState instanceof GameSceneState) {
			$sceneState = GameSceneState::query()
				->where('game_id', $game->id)
				->where('status', 'active')
				->orderByDesc('loaded_at')
				->orderByDesc('id')
				->first();
		}

		if ($sceneState instanceof GameSceneState && $game->active_scene_state_id !== $sceneState->id) {
			$game->forceFill([
				'active_scene_state_id' => $sceneState->id,
			])->save();
		}

		return $sceneState;
	}

	/**
	 * Проверяет, что клетка находится внутри сцены.
	 */
	private function assertCellInsideScene(GameSceneState $sceneState, int $x, int $y, string $message): void
	{
		$sceneTemplate = $sceneState->sceneTemplate()->firstOrFail();

		if ($x < 0 || $y < 0 || $x >= $sceneTemplate->width || $y >= $sceneTemplate->height) {
			throw new RuntimeException($message);
		}
	}

	/**
	 * Проверяет, что клетка свободна для runtime-размещения.
	 */
	private function assertCellIsFreeForRuntime(GameSceneState $sceneState, int $x, int $y): void
	{
		$this->assertCellInsideScene($sceneState, $x, $y, 'Нельзя разместить сущность за пределами активной сцены.');

		$isOccupiedByActor = ActorInstance::query()
			->where('game_scene_state_id', $sceneState->id)
			->where('x', $x)
			->where('y', $y)
			->exists();

		if ($isOccupiedByActor) {
			throw new RuntimeException('Целевая клетка уже занята другим персонажем.');
		}
	}

	/**
	 * Возвращает точки спауна игроков вокруг authored-точки.
	 *
	 * @param array<string, bool> $occupiedCells
	 * @return array<int, array{x:int,y:int}>
	 */
	private function resolvePlayerSpawnCells(GameSceneState $sceneState, Game $game, array $occupiedCells): array
	{
		$spawnPoint = $sceneState->sceneTemplate->metadata['player_spawn_point'] ?? null;

		if (!is_array($spawnPoint) || !isset($spawnPoint['x'], $spawnPoint['y'])) {
			return [];
		}

		$sceneTemplate = $sceneState->sceneTemplate;
		$startX = (int) $spawnPoint['x'];
		$startY = (int) $spawnPoint['y'];
		$neededCount = $game->members->where('role', 'player')->where('status', 'active')->count();
		$queue = [[$startX, $startY]];
		$visited = [];
		$result = [];

		while ($queue !== [] && count($result) < $neededCount) {
			[$x, $y] = array_shift($queue);
			$key = $x.':'.$y;

			if (isset($visited[$key])) {
				continue;
			}

			$visited[$key] = true;

			if ($x >= 0 && $y >= 0 && $x < $sceneTemplate->width && $y < $sceneTemplate->height && !isset($occupiedCells[$key])) {
				$result[] = ['x' => $x, 'y' => $y];
			}

			foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
				$nextX = $x + $dx;
				$nextY = $y + $dy;
				$nextKey = $nextX.':'.$nextY;

				if (isset($visited[$nextKey])) {
					continue;
				}

				if ($nextX < 0 || $nextY < 0 || $nextX >= $sceneTemplate->width || $nextY >= $sceneTemplate->height) {
					continue;
				}

				$queue[] = [$nextX, $nextY];
			}
		}

		return $result;
	}

	/**
	 * Рассчитывает здоровье persistent-актора для runtime.
	 */
	private function resolveActorHitPoints(Actor $actor): int
	{
		if (is_int($actor->health_max) && $actor->health_max > 0) {
			return $actor->health_max;
		}

		return $this->buildActorRuntimeDerivedStats($actor)['health'];
	}

	/**
	 * Рассчитывает производные показатели persistent-актора.
	 *
	 * @return array{health:int,movement_speed:int,armor_class:int,jump_height:int,damage_bonus:int,modifiers:array{str:int,dex:int,con:int}}
	 */
	private function buildActorRuntimeDerivedStats(Actor $actor): array
	{
		return $this->actorCombatStatsService->buildDerivedStats(
			stats: $actor->stats,
			level: $actor->level,
			healthBonus: max(0, (int) ($actor->base_health ?? $this->actorCombatStatsService->baseHealth()) - $this->actorCombatStatsService->baseHealth()),
			speedBonus: max(0, (int) $actor->movement_speed - $this->actorCombatStatsService->baseMovementSpeed()),
			armorBonus: max(0, (int) $actor->armor_class - $this->actorCombatStatsService->baseArmorClass()),
		);
	}

	/**
	 * Рассчитывает производные показатели runtime-героя игрока.
	 *
	 * @return array{health:int,movement_speed:int,armor_class:int,jump_height:int,damage_bonus:int,modifiers:array{str:int,dex:int,con:int}}
	 */
	private function buildPlayerRuntimeDerivedStats(PlayerCharacter $character): array
	{
		return $this->actorCombatStatsService->buildDerivedStats(
			stats: $character->base_stats,
			level: $character->level,
			healthBonus: (int) ($character->derived_stats['health_bonus'] ?? 0),
			speedBonus: (int) ($character->derived_stats['speed_bonus'] ?? 0),
			armorBonus: (int) ($character->derived_stats['armor_bonus'] ?? 0),
		);
	}
}

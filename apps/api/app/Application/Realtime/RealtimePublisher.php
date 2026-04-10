<?php

declare(strict_types=1);

namespace App\Application\Realtime;

use App\Application\Game\SurfaceEffectService;
use App\Models\ActorInstance;
use App\Models\Game;
use App\Models\GameInvitation;
use App\Models\GameSceneState;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Публикует realtime-события для пользовательских кабинетов.
 */
final class RealtimePublisher
{
	private const string CHANNEL_NAME = 'realtime.user-notifications';

	/**
	 * Публикует событие создания приглашения в игру.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishInvitationCreated(GameInvitation $invitation): void
	{
		$this->publishMessage(
			event: 'game-invitation.created',
			targetUserIds: [$invitation->invited_user_id, $invitation->gm_user_id],
			payload: [
				'gameId' => $invitation->game_id,
				'gmUserId' => $invitation->gm_user_id,
				'invitationId' => $invitation->id,
				'invitedUserId' => $invitation->invited_user_id,
				'status' => $invitation->status,
				'token' => $invitation->token,
			],
		);
	}

	/**
	 * Публикует подготовленное realtime-сообщение в Redis.
	 *
	 * @param list<int> $targetUserIds
	 *
	 * @throws Throwable
	 */
	private function publishMessage(string $event, array $targetUserIds, array $payload): void
	{
		if ($targetUserIds === []) {
			return;
		}

		$payload = json_encode([
			'event' => $event,
			'targetUserIds' => array_values(array_unique($targetUserIds)),
			'payload' => $payload,
		], JSON_THROW_ON_ERROR);

		Redis::command('publish', [self::CHANNEL_NAME, $payload]);
	}

	/**
	 * Публикует событие принятия приглашения в игру.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishInvitationAccepted(GameInvitation $invitation): void
	{
		$this->publishMessage(
			event: 'game-invitation.accepted',
			targetUserIds: [$invitation->invited_user_id, $invitation->gm_user_id],
			payload: [
				'gameId' => $invitation->game_id,
				'gmUserId' => $invitation->gm_user_id,
				'invitationId' => $invitation->id,
				'invitedUserId' => $invitation->invited_user_id,
				'status' => $invitation->status,
				'token' => $invitation->token,
			],
		);
	}

	/**
	 * Публикует событие отклонения приглашения в игру.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishInvitationDeclined(GameInvitation $invitation): void
	{
		$this->publishMessage(
			event: 'game-invitation.declined',
			targetUserIds: [$invitation->invited_user_id, $invitation->gm_user_id],
			payload: [
				'gameId' => $invitation->game_id,
				'gmUserId' => $invitation->gm_user_id,
				'invitationId' => $invitation->id,
				'invitedUserId' => $invitation->invited_user_id,
				'status' => $invitation->status,
				'token' => $invitation->token,
			],
		);
	}

	/**
	 * Публикует событие запуска runtime-сцены игры для всех активных игроков стола.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishGameSceneActivated(Game $game, GameSceneState $sceneState): void
	{
		$game->loadMissing('members');
		$sceneState->loadMissing('sceneTemplate:id,name');

		$targetUserIds = $game->members
			->where('role', 'player')
			->where('status', 'active')
			->pluck('user_id')
			->filter(static fn (mixed $userId): bool => is_int($userId))
			->values()
			->all();

		$this->publishMessage(
			event: 'game-scene.activated',
			targetUserIds: $targetUserIds,
			payload: [
				'activeSceneStateId' => $sceneState->id,
				'gameId' => $game->id,
				'gmUserId' => $game->gm_user_id,
				'sceneName' => $sceneState->sceneTemplate?->name,
				'sceneStateId' => $sceneState->id,
			],
		);
	}

	/**
	 * Публикует событие обновления активной runtime-сцены для всех участников игры.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishGameSceneUpdated(Game $game, GameSceneState $sceneState): void
	{
		$game->loadMissing('members');
		$sceneState->loadMissing('sceneTemplate:id,name');

		$targetUserIds = $game->members
			->where('status', 'active')
			->pluck('user_id')
			->filter(static fn (mixed $userId): bool => is_int($userId))
			->push($game->gm_user_id)
			->values()
			->all();

		$this->publishMessage(
			event: 'game-scene.updated',
			targetUserIds: $targetUserIds,
			payload: [
				'activeSceneStateId' => $sceneState->id,
				'gameId' => $game->id,
				'gmUserId' => $game->gm_user_id,
				'sceneName' => $sceneState->sceneTemplate?->name,
				'sceneStateId' => $sceneState->id,
				'version' => $sceneState->version,
			],
		);
	}

	/**
	 * Публикует событие перемещения runtime-актора.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishRuntimeActorMoved(Game $game, GameSceneState $sceneState, ActorInstance $actorInstance): void
	{
		$this->publishGameSceneDelta($game, $sceneState, 'game-scene.actor-moved', [
			'actor' => $this->buildActorPayload($actorInstance),
		]);
	}

	/**
	 * Публикует событие появления runtime-актора на сцене.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishRuntimeActorSpawned(Game $game, GameSceneState $sceneState, ActorInstance $actorInstance): void
	{
		$this->publishGameSceneDelta($game, $sceneState, 'game-scene.actor-spawned', [
			'actor' => $this->buildActorPayload($actorInstance),
		]);
	}

	/**
	 * Публикует событие изменения поверхности клетки.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishRuntimeCellPainted(
		Game $game,
		GameSceneState $sceneState,
		int $x,
		int $y,
		string $terrainType,
		bool $isPassable,
		bool $blocksVision,
	): void
	{
		$this->publishGameSceneDelta($game, $sceneState, 'game-scene.cell-painted', [
			'cell' => [
				'x' => $x,
				'y' => $y,
				'terrain_type' => $terrainType,
				'is_passable' => $isPassable,
				'blocks_vision' => $blocksVision,
			],
		]);
	}

	/**
	 * Публикует событие появления предмета на сцене.
	 *
	 * @param array{id:string,item_code:string,name:string,quantity:int,x:int,y:int,image_url:?string} $itemDrop
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishRuntimeItemDropped(Game $game, GameSceneState $sceneState, array $itemDrop): void
	{
		$this->publishGameSceneDelta($game, $sceneState, 'game-scene.item-dropped', [
			'itemDrop' => $itemDrop,
		]);
	}

	/**
	 * Публикует versioned delta-событие runtime-сцены.
	 *
	 * @param array<string, mixed> $payload
	 *
	 * @throws Throwable
	 */
	private function publishGameSceneDelta(Game $game, GameSceneState $sceneState, string $event, array $payload): void
	{
		$game->loadMissing('members');
		$sceneState->loadMissing('sceneTemplate:id,name');

		$targetUserIds = $game->members
			->where('status', 'active')
			->pluck('user_id')
			->filter(static fn (mixed $userId): bool => is_int($userId))
			->push($game->gm_user_id)
			->values()
			->all();

		$this->publishMessage(
			event: $event,
			targetUserIds: $targetUserIds,
			payload: [
				...$payload,
				'activeSceneStateId' => $sceneState->id,
				'gameId' => $game->id,
				'gmUserId' => $game->gm_user_id,
				'sceneName' => $sceneState->sceneTemplate?->name,
				'sceneStateId' => $sceneState->id,
				'version' => $sceneState->version,
			],
		);
	}

	/**
	 * Собирает сериализуемое представление runtime-актора для realtime.
	 *
	 * @return array
	 */
	private function buildActorPayload(ActorInstance $actorInstance): array
	{
		$surfaceEffectService = app(SurfaceEffectService::class);

		return [
			'id' => $actorInstance->id,
			'game_id' => $actorInstance->game_id,
			'game_scene_state_id' => $actorInstance->game_scene_state_id,
			'player_character_id' => $actorInstance->player_character_id,
			'controlled_by_user_id' => $actorInstance->controlled_by_user_id,
			'kind' => $actorInstance->kind,
			'controller_type' => $actorInstance->controller_type,
			'name' => $actorInstance->name,
			'faction' => $actorInstance->faction,
			'social_state' => $actorInstance->social_state,
			'status' => $actorInstance->status,
			'x' => $actorInstance->x,
			'y' => $actorInstance->y,
			'initiative' => $actorInstance->initiative,
			'hp_current' => $actorInstance->hp_current,
			'hp_max' => $actorInstance->hp_max,
			'is_hidden' => (bool) $actorInstance->is_hidden,
			'resources' => $actorInstance->resources,
			'temporary_effects' => $surfaceEffectService->activeEffects($actorInstance),
			'runtime_state' => $actorInstance->runtime_state,
			'image_url' => $actorInstance->image_url,
			'movement_speed' => $surfaceEffectService->resolveEffectiveMovementSpeed($actorInstance),
		];
	}
}

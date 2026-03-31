<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Application\Realtime\RealtimePublisher;
use App\Data\Game\InviteGameMemberData;
use App\Models\Game;
use App\Models\GameInvitation;
use App\Models\GameMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Управляет приглашениями игроков в игровые столы.
 */
final readonly class GameInvitationService
{
	/**
	 * Создает сервис приглашений в игровые столы.
	 */
	public function __construct(
		private readonly GameManagementService $gameManagementService,
		private readonly RealtimePublisher     $realtimePublisher,
	)
	{
	}

	/**
	 * Создает приглашение игроку в игру текущего мастера.
	 *
	 * @throws RuntimeException Если пользователь не найден, уже состоит в игре или уже приглашен.
	 * @throws Throwable Если создание приглашения завершилось технической ошибкой.
	 */
	public function inviteMember(int $gameId, InviteGameMemberData $data, User $user): ?Game
	{
		/** @var Game|null $game */
		$game = Game::query()
			->where('id', $gameId)
			->where('gm_user_id', $user->id)
			->first();

		if ($game === null) {
			return null;
		}

		/** @var User|null $invitedUser */
		$invitedUser = User::query()
			->where('email', $data->login)
			->orWhere('name', $data->login)
			->first();

		if ($invitedUser === null) {
			throw new RuntimeException('Пользователь с таким логином или email не найден.');
		}

		if ($invitedUser->id === $user->id) {
			throw new RuntimeException('Мастер уже состоит в своей игре.');
		}

		$memberExists = GameMember::query()
			->where('game_id', $game->id)
			->where('user_id', $invitedUser->id)
			->exists();

		if ($memberExists) {
			throw new RuntimeException('Этот пользователь уже добавлен в игру.');
		}

		$pendingInvitationExists = GameInvitation::query()
			->where('game_id', $game->id)
			->where('invited_user_id', $invitedUser->id)
			->where('status', 'pending')
			->exists();

		if ($pendingInvitationExists) {
			throw new RuntimeException('Этому пользователю уже отправлено приглашение.');
		}

		DB::transaction(static function () use ($game, $invitedUser, $user): void {
			GameInvitation::query()->create([
				'game_id' => $game->id,
				'gm_user_id' => $user->id,
				'invited_user_id' => $invitedUser->id,
				'token' => (string)Str::uuid(),
				'status' => 'pending',
				'sent_at' => now(),
			]);
		});

		/** @var GameInvitation $createdInvitation */
		$createdInvitation = GameInvitation::query()
			->where('game_id', $game->id)
			->where('invited_user_id', $invitedUser->id)
			->where('status', 'pending')
			->latest('id')
			->firstOrFail();

		$this->realtimePublisher->publishInvitationCreated($createdInvitation);

		/** @var Game $updatedGame */
		$updatedGame = $this->gameManagementService->findGameForGameMaster($game->id, $user);

		return $updatedGame;
	}

	/**
	 * Возвращает приглашения текущего игрока.
	 *
	 * @return Collection<int, GameInvitation>
	 */
	public function getInvitationsForPlayer(User $user): Collection
	{
		return GameInvitation::query()
			->where('invited_user_id', $user->id)
			->with([
				'game:id,title,description,status,gm_user_id',
				'gm:id,name,email',
			])
			->latest('id')
			->get();
	}

	/**
	 * Принимает приглашение игрока и добавляет его в игру.
	 *
	 * @throws RuntimeException Если приглашение не найдено, не принадлежит игроку или уже обработано.
	 * @throws Throwable Если принятие приглашения завершилось технической ошибкой.
	 */
	public function acceptInvitation(string $token, User $user): ?GameInvitation
	{
		/** @var GameInvitation|null $invitation */
		$invitation = GameInvitation::query()
			->where('token', $token)
			->with(['game:id,title,description,status,gm_user_id', 'gm:id,name,email'])
			->first();

		if ($invitation === null) {
			return null;
		}

		if ($invitation->invited_user_id !== $user->id) {
			throw new RuntimeException('Это приглашение принадлежит другому пользователю.');
		}

		if ($invitation->status !== 'pending') {
			throw new RuntimeException('Это приглашение уже обработано.');
		}

		DB::transaction(function () use ($invitation, $user): void {
			$memberExists = GameMember::query()
				->where('game_id', $invitation->game_id)
				->where('user_id', $user->id)
				->exists();

			if (!$memberExists) {
				GameMember::query()->create([
					'game_id' => $invitation->game_id,
					'user_id' => $user->id,
					'role' => 'player',
					'status' => 'active',
					'joined_at' => now(),
				]);
			}

			$invitation->fill([
				'status' => 'accepted',
				'responded_at' => now(),
			]);
			$invitation->save();
		});

		$invitation->load(['game:id,title,description,status,gm_user_id', 'gm:id,name,email']);

		$this->realtimePublisher->publishInvitationAccepted($invitation);

		return $invitation;
	}

	/**
	 * Отклоняет приглашение игрока без добавления его в игру.
	 *
	 * @throws RuntimeException Если приглашение не найдено, не принадлежит игроку или уже обработано.
	 * @throws Throwable Если отклонение приглашения завершилось технической ошибкой.
	 */
	public function declineInvitation(string $token, User $user): ?GameInvitation
	{
		/** @var GameInvitation|null $invitation */
		$invitation = GameInvitation::query()
			->where('token', $token)
			->with(['game:id,title,description,status,gm_user_id', 'gm:id,name,email'])
			->first();

		if ($invitation === null) {
			return null;
		}

		if ($invitation->invited_user_id !== $user->id) {
			throw new RuntimeException('Это приглашение принадлежит другому пользователю.');
		}

		if ($invitation->status !== 'pending') {
			throw new RuntimeException('Это приглашение уже обработано.');
		}

		DB::transaction(function () use ($invitation): void {
			$invitation->fill([
				'status' => 'declined',
				'responded_at' => now(),
			]);
			$invitation->save();
		});

		$invitation->load(['game:id,title,description,status,gm_user_id', 'gm:id,name,email']);

		$this->realtimePublisher->publishInvitationDeclined($invitation);

		return $invitation;
	}
}

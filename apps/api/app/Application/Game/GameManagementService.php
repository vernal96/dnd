<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Data\Game\CreateGameData;
use App\Data\Game\GameListFiltersData;
use App\Data\Game\UpdateGameStatusData;
use App\Models\Game;
use App\Models\GameMember;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Управляет созданием игр и чтением кабинета мастера.
 */
final class GameManagementService
{
	/**
	 * Возвращает список игр, которыми управляет текущий мастер.
	 *
	 * @return LengthAwarePaginator<int, Game>
	 */
	public function getGamesForGameMaster(User $user, GameListFiltersData $filters): LengthAwarePaginator
	{
		$query = Game::query()
			->where('gm_user_id', $user->id)
			->with([
				'gm:id,name,email',
				'members.user:id,name,email',
				'members.playerCharacter:id,user_id,name,description,race,subrace,class,level,experience,status,image_path,created_at,updated_at',
				'activeSceneState:id,game_id,scene_template_id,status,version',
				'sceneStates:id,game_id,scene_template_id,status,version,created_at,updated_at',
				'sceneStates.sceneTemplate:id,name,description,width,height,status,metadata,created_at,updated_at',
			])
			->withCount('members')
			->latest('id');

		if ($filters->status !== null) {
			$query->where('status', $filters->status);
		}

		return $query->paginate(20);
	}

	/**
	 * Создает новую игру и сразу добавляет мастера в список участников.
	 *
	 * @throws Throwable Если создание игры завершилось технической ошибкой.
	 */
	public function createGame(CreateGameData $data, User $user): Game
	{
		/** @var Game $game */
		$game = DB::transaction(static function () use ($data, $user): Game {
			/** @var Game $createdGame */
			$createdGame = Game::query()->create([
				'title' => $data->title,
				'description' => $data->description,
				'gm_user_id' => $user->id,
				'status' => 'draft',
			]);

			$createdGame->members()->create([
				'game_id' => $createdGame->id,
				'user_id' => $user->id,
				'player_character_id' => null,
				'role' => 'gm',
				'status' => 'active',
				'joined_at' => now(),
			]);

			return $createdGame;
		});

		$game->load([
			'gm:id,name,email',
			'members.user:id,name,email',
			'members.playerCharacter:id,user_id,name,description,race,subrace,class,level,experience,status,image_path,created_at,updated_at',
			'activeSceneState:id,game_id,scene_template_id,status,version',
			'sceneStates:id,game_id,scene_template_id,status,version,created_at,updated_at',
			'sceneStates.sceneTemplate:id,name,description,width,height,status,metadata,created_at,updated_at',
		])
			->loadCount('members');

		return $game;
	}

	/**
	 * Обновляет статус игры, принадлежащей текущему мастеру.
	 *
	 * @throws Throwable Если обновление статуса завершилось технической ошибкой.
	 */
	public function updateGameStatus(int $gameId, UpdateGameStatusData $data, User $user): ?Game
	{
		/** @var Game|null $game */
		$game = Game::query()
			->where('id', $gameId)
			->where('gm_user_id', $user->id)
			->first();

		if ($game === null) {
			return null;
		}

		$game->fill([
			'status' => $data->status,
			'started_at' => $data->status === 'active' && $game->started_at === null ? now() : $game->started_at,
			'paused_at' => $data->status === 'paused' ? now() : null,
			'completed_at' => $data->status === 'completed' ? now() : null,
		]);

		if ($data->status !== 'completed') {
			$game->completed_at = null;
		}

		if ($data->status !== 'paused') {
			$game->paused_at = null;
		}

		$game->save();

			$game->load([
			'gm:id,name,email',
			'members.user:id,name,email',
			'members.playerCharacter:id,user_id,name,description,race,subrace,class,level,experience,status,image_path,created_at,updated_at',
			'invitations' => static function ($query): void {
				$query
					->where('status', 'pending')
					->latest('id');
			},
			'invitations.invitedUser:id,name,email',
			'activeSceneState.sceneTemplate:id,name,width,height,status',
			'sceneStates:id,game_id,scene_template_id,status,version,created_at,updated_at',
			'sceneStates.sceneTemplate:id,name,description,width,height,status,metadata,created_at,updated_at',
		])->loadCount('members');

		return $game;
	}

	/**
	 * Удаляет участника из игры текущего мастера.
	 *
	 * @throws RuntimeException Если мастер пытается удалить себя из собственной игры.
	 * @throws Throwable Если удаление участника завершилось технической ошибкой.
	 */
	public function removeMember(int $gameId, int $memberId, User $user): ?Game
	{
		/** @var Game|null $game */
		$game = Game::query()
			->where('id', $gameId)
			->where('gm_user_id', $user->id)
			->first();

		if ($game === null) {
			return null;
		}

		/** @var GameMember|null $member */
		$member = GameMember::query()
			->where('id', $memberId)
			->where('game_id', $game->id)
			->first();

		if ($member === null) {
			return null;
		}

		if ($member->user_id === $user->id || $member->role === 'gm') {
			throw new RuntimeException('Нельзя удалить мастера из собственной игры.');
		}

		DB::transaction(static function () use ($member): void {
			$member->delete();
		});

		/** @var Game $updatedGame */
		$updatedGame = $this->findGameForGameMaster($game->id, $user);

		return $updatedGame;
	}

	/**
	 * Возвращает одну игру, принадлежащую текущему мастеру.
	 */
	public function findGameForGameMaster(int $gameId, User $user): ?Game
	{
		return Game::query()
			->where('id', $gameId)
			->where('gm_user_id', $user->id)
			->with([
				'gm:id,name,email',
				'members.user:id,name,email',
				'members.playerCharacter:id,user_id,name,description,race,subrace,class,level,experience,status,image_path,created_at,updated_at',
				'invitations' => static function ($query): void {
					$query
						->where('status', 'pending')
						->latest('id');
				},
				'invitations.invitedUser:id,name,email',
				'activeSceneState.sceneTemplate:id,name,width,height,status',
				'sceneStates:id,game_id,scene_template_id,status,version,created_at,updated_at',
				'sceneStates.sceneTemplate:id,name,description,width,height,status,metadata,created_at,updated_at',
			])
			->withCount('members')
			->first();
	}
}

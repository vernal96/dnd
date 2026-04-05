<?php

declare(strict_types=1);

namespace App\Application\Player;

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Отвечает за чтение активных игр игрока и доступ к ним из кабинета.
 */
final class PlayerGameParticipationService
{
	/**
	 * Возвращает список активных игр, в которых участвуют персонажи текущего игрока.
	 *
	 * @return Collection<int, Game>
	 */
	public function getActiveGamesForPlayer(User $user): Collection
	{
		return Game::query()
			->whereNotNull('active_scene_state_id')
			->where('status', '!=', 'completed')
			->whereHas('members', static function ($query) use ($user): void {
				$query
					->where('user_id', $user->id)
					->where('role', 'player')
					->where('status', 'active')
					->whereNotNull('player_character_id');
			})
			->with([
				'gm:id,name,email',
				'activeSceneState:id,game_id,scene_template_id,status,version,loaded_at',
				'activeSceneState.sceneTemplate:id,name,width,height,status',
				'members' => static function ($query) use ($user): void {
					$query
						->where('user_id', $user->id)
						->where('role', 'player')
						->where('status', 'active');
				},
				'members.user:id,name,email',
				'members.playerCharacter:id,user_id,name,description,race,subrace,class,level,experience,status,image_path,created_at,updated_at',
			])
			->latest('id')
			->get();
	}

	/**
	 * Возвращает активную игру текущего игрока по идентификатору.
	 */
	public function findActiveGameForPlayer(int $gameId, User $user): ?Game
	{
		return Game::query()
			->where('id', $gameId)
			->whereNotNull('active_scene_state_id')
			->where('status', '!=', 'completed')
			->whereHas('members', static function ($query) use ($user): void {
				$query
					->where('user_id', $user->id)
					->where('role', 'player')
					->where('status', 'active')
					->whereNotNull('player_character_id');
			})
			->with([
				'gm:id,name,email',
				'activeSceneState:id,game_id,scene_template_id,status,version,loaded_at',
				'activeSceneState.sceneTemplate:id,name,width,height,status',
				'members' => static function ($query) use ($user): void {
					$query
						->where('user_id', $user->id)
						->where('role', 'player')
						->where('status', 'active');
				},
				'members.user:id,name,email',
				'members.playerCharacter:id,user_id,name,description,race,subrace,class,level,experience,status,image_path,created_at,updated_at',
			])
			->first();
	}
}

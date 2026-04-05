<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Data\Game\CreateActorData;
use App\Data\Game\UpdateActorData;
use App\Models\Actor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Управляет библиотекой persistent-акторов текущего мастера.
 */
final class ActorManagementService
{
	/**
	 * Возвращает список persistent-акторов текущего мастера.
	 */
	public function getActorsForGameMaster(User $user): Collection
	{
		return Actor::query()
			->where('gm_user_id', $user->id)
			->with('gameMaster:id,name,email')
			->orderBy('name')
			->orderBy('id')
			->get();
	}

	/**
	 * Создает нового persistent-актора в библиотеке мастера.
	 *
	 * @throws Throwable Если создание актора завершилось технической ошибкой.
	 */
	public function createActor(CreateActorData $data, User $user): Actor
	{
		/** @var Actor $actor */
		$actor = DB::transaction(function () use ($data, $user): Actor {
			return Actor::query()->create([
				'gm_user_id' => $user->id,
				'kind' => $data->kind,
				'name' => $data->name,
				'description' => $data->description,
				'race' => $data->race,
				'character_class' => $data->characterClass,
				'level' => $data->level,
				'movement_speed' => $data->movementSpeed,
				'base_health' => $data->baseHealth,
				'health_current' => $data->healthCurrent,
				'health_max' => $data->healthMax,
				'stats' => $data->stats,
				'inventory' => $data->inventory,
				'image_path' => $data->imagePath,
				'meta' => $data->meta,
			]);
		});

		return $this->loadActor($actor);
	}

	/**
	 * Возвращает одного persistent-актора из библиотеки текущего мастера.
	 */
	public function findActorForGameMaster(int $actorId, User $user): ?Actor
	{
		$actor = $this->findOwnedActor($actorId, $user);

		if ($actor === null) {
			return null;
		}

		return $this->loadActor($actor);
	}

	/**
	 * Полностью обновляет persistent-актора текущего мастера.
	 *
	 * @throws Throwable Если обновление актора завершилось технической ошибкой.
	 */
	public function updateActor(int $actorId, UpdateActorData $data, User $user): ?Actor
	{
		$actor = $this->findOwnedActor($actorId, $user);

		if ($actor === null) {
			return null;
		}

		DB::transaction(function () use ($actor, $data): void {
			$actor->fill([
				'kind' => $data->kind,
				'name' => $data->name,
				'description' => $data->description,
				'race' => $data->race,
				'character_class' => $data->characterClass,
				'level' => $data->level,
				'movement_speed' => $data->movementSpeed,
				'base_health' => $data->baseHealth,
				'health_current' => $data->healthCurrent,
				'health_max' => $data->healthMax,
				'stats' => $data->stats,
				'inventory' => $data->inventory,
				'image_path' => $data->imagePath,
				'meta' => $data->meta,
			]);
			$actor->save();
		});

		return $this->loadActor($actor);
	}

	/**
	 * Удаляет persistent-актора из библиотеки текущего мастера.
	 *
	 * @throws Throwable Если удаление актора завершилось технической ошибкой.
	 */
	public function deleteActor(int $actorId, User $user): bool
	{
		$actor = $this->findOwnedActor($actorId, $user);

		if ($actor === null) {
			return false;
		}

		DB::transaction(function () use ($actor): void {
			$actor->delete();
		});

		return true;
	}

	/**
	 * Возвращает persistent-актора, принадлежащего текущему мастеру.
	 */
	private function findOwnedActor(int $actorId, User $user): ?Actor
	{
		return Actor::query()
			->where('id', $actorId)
			->where('gm_user_id', $user->id)
			->first();
	}

	/**
	 * Загружает минимальный контекст актора, нужный API кабинета.
	 */
	private function loadActor(Actor $actor): Actor
	{
		$actor->load('gameMaster:id,name,email');

		return $actor;
	}
}

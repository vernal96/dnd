<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Game;
use App\Models\GameMember;
use App\Models\GameSceneState;
use App\Models\PlayerCharacter;
use App\Models\SceneTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Заполняет локальную среду воспроизводимым demo-набором игры, сцены и персонажей.
 */
final class LocalDevelopmentDemoSeeder extends Seeder
{
	/**
	 * Создает demo-данные для локальной разработки без дублирования записей.
	 */
	public function run(): void
	{
		$gameMaster = User::query()->where('email', 'gm@tavern.local')->firstOrFail();
		$player = User::query()->where('email', 'player@tavern.local')->firstOrFail();

		$game = Game::query()->updateOrCreate(
			[
				'gm_user_id' => $gameMaster->id,
				'title' => 'Таверна у Старой дороги',
			],
			[
				'description' => 'Демо-стол для локальной разработки: сцена таверны, NPC мастера и один персонаж игрока.',
				'status' => 'active',
				'settings' => [
					'theme' => 'local-dev',
				],
			],
		);

		GameMember::query()->updateOrCreate(
			[
				'game_id' => $game->id,
				'user_id' => $player->id,
			],
			[
				'role' => 'player',
				'status' => 'active',
				'joined_at' => now(),
			],
		);

		$playerCharacter = PlayerCharacter::query()->updateOrCreate(
			[
				'user_id' => $player->id,
				'name' => 'Элиан',
			],
			[
				'description' => 'Молодой следопыт, который ищет пропавший караван у Старой дороги.',
				'race' => 'elf',
				'subrace' => 'wood-elf',
				'class' => 'ranger',
				'level' => 3,
				'experience' => 900,
				'status' => 'active',
				'base_stats' => [
					'strength' => 9,
					'dexterity' => 17,
					'constitution' => 13,
					'intelligence' => 11,
					'wisdom' => 15,
					'charisma' => 10,
				],
				'derived_stats' => [
					'armor_class' => 14,
					'initiative' => 3,
					'passive_perception' => 14,
					'speed' => 7,
				],
				'image_path' => 'player-characters/' . $player->id . '/elian-dev.png',
				'unlocked_skills' => ['survival', 'perception', 'stealth'],
				'meta' => [
					'seed' => 'local-development-demo',
				],
			],
		);
		$this->seedPlayerCharacterImage($playerCharacter, $player);

		$narratorNpc = Actor::query()->updateOrCreate(
			[
				'gm_user_id' => $gameMaster->id,
				'name' => 'Бран Кривой Кубок',
			],
			[
				'kind' => 'npc',
				'description' => 'Хозяин таверны, знает все слухи и первым замечает странных гостей.',
				'race' => 'human',
				'character_class' => 'fighter',
				'level' => 2,
				'movement_speed' => 6,
				'base_health' => 18,
				'health_current' => 18,
				'health_max' => 18,
				'stats' => [
					'strength' => 13,
					'dexterity' => 10,
					'constitution' => 14,
					'intelligence' => 10,
					'wisdom' => 12,
					'charisma' => 13,
				],
				'inventory' => [
					[
						'item_code' => 'dagger',
						'quantity' => 1,
						'is_equipped' => true,
						'slot' => 'main-hand',
						'state' => null,
					],
				],
				'image_path' => 'gm-actors/' . $gameMaster->id . '/bran-dev.png',
				'meta' => [
					'seed' => 'local-development-demo',
				],
			],
		);
		$this->seedActorImage($narratorNpc, $gameMaster);

		$sceneTemplate = SceneTemplate::query()->updateOrCreate(
			[
				'created_by' => $gameMaster->id,
				'name' => 'Главный зал таверны',
			],
			[
				'description' => 'Стартовая demo-сцена с деревянным полом, бочками и хозяином таверны.',
				'width' => 8,
				'height' => 8,
				'status' => 'draft',
				'metadata' => [
					'viewport' => [
						'offsetX' => 0,
						'offsetY' => 0,
						'rotateX' => 45,
						'rotateZ' => 45,
						'zoom' => 1,
					],
				],
			],
		);

		$this->seedCells($sceneTemplate);
		$this->seedObjects($sceneTemplate);
		$this->seedActorPlacement($sceneTemplate, $narratorNpc);

		$sceneState = GameSceneState::query()->updateOrCreate(
			[
				'game_id' => $game->id,
				'scene_template_id' => $sceneTemplate->id,
			],
			[
				'status' => 'prepared',
				'version' => 1,
				'runtime_state' => [
					'seed' => 'local-development-demo',
				],
			],
		);

		if ($game->active_scene_state_id === null) {
			$game->forceFill([
				'active_scene_state_id' => $sceneState->id,
			])->save();
		}

		$playerCharacter->refresh();
	}

	/**
	 * Копирует demo-изображение персонажа игрока в ожидаемый каталог storage.
	 */
	private function seedPlayerCharacterImage(PlayerCharacter $character, User $user): void
	{
		$disk = Storage::disk('game_images');
		$targetPath = 'player-characters/' . $user->id . '/elian-dev.png';

		if (!$disk->exists('cultist.png')) {
			return;
		}

		if (!$disk->exists($targetPath)) {
			$disk->copy('cultist.png', $targetPath);
		}

		if ($character->image_path !== $targetPath) {
			$character->forceFill([
				'image_path' => $targetPath,
			])->save();
		}
	}

	/**
	 * Копирует demo-изображение NPC в ожидаемый каталог storage.
	 */
	private function seedActorImage(Actor $actor, User $user): void
	{
		$disk = Storage::disk('game_images');
		$targetPath = 'gm-actors/' . $user->id . '/bran-dev.png';

		if (!$disk->exists('orc.png')) {
			return;
		}

		if (!$disk->exists($targetPath)) {
			$disk->copy('orc.png', $targetPath);
		}

		if ($actor->image_path !== $targetPath) {
			$actor->forceFill([
				'image_path' => $targetPath,
			])->save();
		}
	}

	/**
	 * Создает или обновляет клетки authored-сцены.
	 */
	private function seedCells(SceneTemplate $sceneTemplate): void
	{
		for ($y = 0; $y < $sceneTemplate->height; $y += 1) {
			for ($x = 0; $x < $sceneTemplate->width; $x += 1) {
				$terrainType = 'soil';

				if ($x === 0 || $y === 0 || $x === $sceneTemplate->width - 1 || $y === $sceneTemplate->height - 1) {
					$terrainType = 'stone';
				}

				if ($x >= 2 && $x <= 5 && $y >= 2 && $y <= 5) {
					$terrainType = 'grass';
				}

				$sceneTemplate->cells()->updateOrCreate(
					[
						'x' => $x,
						'y' => $y,
					],
					[
						'terrain_type' => $terrainType,
						'elevation' => 0,
						'is_passable' => true,
						'blocks_vision' => false,
						'props' => null,
					],
				);
			}
		}
	}

	/**
	 * Создает или обновляет authored-объекты demo-сцены.
	 */
	private function seedObjects(SceneTemplate $sceneTemplate): void
	{
		$sceneTemplate->objects()->updateOrCreate(
			[
				'kind' => 'barrel',
				'x' => 1,
				'y' => 1,
			],
			[
				'name' => 'Бочка с элем',
				'width' => 1,
				'height' => 1,
				'is_hidden' => false,
				'is_interactive' => true,
				'state' => null,
			],
		);

		$sceneTemplate->objects()->updateOrCreate(
			[
				'kind' => 'bush',
				'x' => 6,
				'y' => 5,
			],
			[
				'name' => 'Куст у входа',
				'width' => 1,
				'height' => 1,
				'is_hidden' => false,
				'is_interactive' => false,
				'state' => null,
			],
		);
	}

	/**
	 * Создает или обновляет размещение seeded NPC на authored-сцене.
	 */
	private function seedActorPlacement(SceneTemplate $sceneTemplate, Actor $actor): void
	{
		$sceneTemplate->actorPlacements()->updateOrCreate(
			[
				'actor_id' => $actor->id,
			],
			[
				'x' => 3,
				'y' => 2,
			],
		);
	}
}

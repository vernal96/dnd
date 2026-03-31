<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Проверяет API хранения изображений игровых столов.
 */
final class GameImageControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Возвращает стандартные заголовки запросов нашего frontend.
     *
     * @return array{Origin:string,Referer:string,Accept:string}
     */
    private function frontendHeaders(): array
    {
        return [
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Аутентифицирует пользователя через session API и возвращает CSRF токен.
     */
    private function authenticate(User $user): string
    {
        $sessionResponse = $this->getJson('/api/auth/session', $this->frontendHeaders());
        $csrfToken = (string) $sessionResponse->json('csrfToken');

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'secret-pass',
            'remember' => false,
        ])->assertOk();

        return (string) $this->getJson('/api/auth/session', $this->frontendHeaders())->json('csrfToken');
    }

    /**
     * Проверяет, что мастер может загрузить изображение игры.
     */
    public function test_game_master_can_upload_game_image(): void
    {
        Storage::fake('game_images');

        $gameMaster = User::query()->create([
            'name' => 'gm-one',
            'email' => 'gm-one@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        $game = Game::query()->create([
            'title' => 'Карта мира',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        GameMember::query()->create([
            'game_id' => $game->id,
            'user_id' => $gameMaster->id,
            'role' => 'gm',
            'status' => 'active',
        ]);

        $csrfToken = $this->authenticate($gameMaster);
        $file = UploadedFile::fake()->create('world-map.png', 128, 'image/png');

        $response = $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->post('/api/games/'.$game->id.'/images', [
            'file' => $file,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('originalName', 'world-map.png')
            ->assertJsonPath('mimeType', 'image/png');

        $storedFileName = (string) $response->json('fileName');
        Storage::disk('game_images')->assertExists('games/'.$game->id.'/'.$storedFileName);
    }

    /**
     * Проверяет, что участник игры может получить список изображений и открыть файл.
     */
    public function test_game_member_can_view_game_images(): void
    {
        Storage::fake('game_images');

        $gameMaster = User::query()->create([
            'name' => 'gm-one',
            'email' => 'gm-one@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        $player = User::query()->create([
            'name' => 'player-one',
            'email' => 'player@example.com',
            'password' => 'secret-pass',
        ]);

        $game = Game::query()->create([
            'title' => 'Карта мира',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        GameMember::query()->create([
            'game_id' => $game->id,
            'user_id' => $gameMaster->id,
            'role' => 'gm',
            'status' => 'active',
        ]);

        GameMember::query()->create([
            'game_id' => $game->id,
            'user_id' => $player->id,
            'role' => 'player',
            'status' => 'active',
        ]);

        Storage::disk('game_images')->put('games/'.$game->id.'/map.png', 'fake-image-content');

        $this->authenticate($player);

        $this->getJson('/api/games/'.$game->id.'/images', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('0.fileName', 'map.png');

        $this->get('/api/games/'.$game->id.'/images/map.png', $this->frontendHeaders())
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename="map.png"');
    }
}

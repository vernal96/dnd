<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Проверяет API загрузки изображений персонажей игрока.
 */
final class PlayerCharacterImageControllerTest extends TestCase
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
	 * Проверяет загрузку и открытие изображения персонажа игрока.
	 */
	public function test_player_can_upload_and_open_character_image(): void
	{
		Storage::fake('game_images');

		$user = User::query()->create([
			'name' => 'player-one',
			'email' => 'player@example.com',
			'password' => 'secret-pass',
		]);

		$csrfToken = $this->authenticate($user);
		$file = UploadedFile::fake()->create('hero-avatar.png', 128, 'image/png');

		$response = $this->withHeaders([
			...$this->frontendHeaders(),
			'X-CSRF-TOKEN' => $csrfToken,
		])->post('/api/player/character-images', [
			'file' => $file,
		]);

		$response
			->assertCreated()
			->assertJsonPath('originalName', 'hero-avatar.png')
			->assertJsonPath('mimeType', 'image/png')
			->assertJsonPath('storagePath', 'player-characters/' . $user->id . '/' . $response->json('fileName'));

		$storedFileName = (string) $response->json('fileName');
		Storage::disk('game_images')->assertExists('player-characters/' . $user->id . '/' . $storedFileName);

		$this->get('/api/player/character-images/' . $storedFileName, $this->frontendHeaders())
			->assertOk()
			->assertHeader('content-disposition', 'inline; filename="' . $storedFileName . '"');
	}
}

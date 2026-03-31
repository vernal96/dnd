<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameInvitation;
use App\Models\GameMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет сценарии кабинета мастера для списка и создания игр.
 */
final class GameControllerTest extends TestCase
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
     * Проверяет, что мастер получает только свои игры.
     */
    public function test_game_master_sees_only_his_games(): void
    {
        $gameMaster = User::query()->create([
            'name' => 'gm-one',
            'email' => 'gm-one@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        $anotherGameMaster = User::query()->create([
            'name' => 'gm-two',
            'email' => 'gm-two@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        Game::query()->create([
            'title' => 'Башня заката',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        Game::query()->create([
            'title' => 'Чужая игра',
            'gm_user_id' => $anotherGameMaster->id,
            'status' => 'draft',
        ]);

        $this->authenticate($gameMaster);

        $this->getJson('/api/games', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Башня заката')
            ->assertJsonMissing(['title' => 'Чужая игра']);
    }

    /**
     * Проверяет, что мастер может фильтровать свои игры по статусу.
     */
    public function test_game_master_can_filter_games_by_status(): void
    {
        $gameMaster = User::query()->create([
            'name' => 'gm-one',
            'email' => 'gm-one@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        Game::query()->create([
            'title' => 'Черновик',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        Game::query()->create([
            'title' => 'Активная партия',
            'gm_user_id' => $gameMaster->id,
            'status' => 'active',
        ]);

        $this->authenticate($gameMaster);

        $this->getJson('/api/games?status=active', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Активная партия')
            ->assertJsonMissing(['title' => 'Черновик']);
    }

    /**
     * Проверяет, что мастер может создать новую игру.
     */
    public function test_game_master_can_create_game(): void
    {
        $gameMaster = User::query()->create([
            'name' => 'gm-one',
            'email' => 'gm-one@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        $csrfToken = $this->authenticate($gameMaster);

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/games', [
            'title' => 'Подземелье красной луны',
            'description' => 'Тестовая игра для кабинета мастера.',
        ])
            ->assertCreated()
            ->assertJsonPath('title', 'Подземелье красной луны')
            ->assertJsonPath('gm.id', $gameMaster->id)
            ->assertJsonPath('members_count', 1);

        $this->assertDatabaseHas('games', [
            'title' => 'Подземелье красной луны',
            'gm_user_id' => $gameMaster->id,
        ]);

        $this->assertDatabaseHas('game_members', [
            'role' => 'gm',
            'user_id' => $gameMaster->id,
        ]);
    }

    /**
     * Проверяет, что мастер может открыть свою игру, но не чужую.
     */
    public function test_game_master_can_view_only_his_own_game(): void
    {
        $gameMaster = User::query()->create([
            'name' => 'gm-one',
            'email' => 'gm-one@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        $anotherGameMaster = User::query()->create([
            'name' => 'gm-two',
            'email' => 'gm-two@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        $ownGame = Game::query()->create([
            'title' => 'Моя игра',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        $foreignGame = Game::query()->create([
            'title' => 'Чужая игра',
            'gm_user_id' => $anotherGameMaster->id,
            'status' => 'draft',
        ]);

        $this->authenticate($gameMaster);

        $this->getJson('/api/games/'.$ownGame->id, $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('title', 'Моя игра');

        $this->getJson('/api/games/'.$foreignGame->id, $this->frontendHeaders())
            ->assertNotFound()
            ->assertJsonPath('message', 'Игра не найдена.');
    }

    /**
     * Проверяет, что мастер может обновить статус своей игры.
     */
    public function test_game_master_can_update_game_status(): void
    {
        $gameMaster = User::query()->create([
            'name' => 'gm-one',
            'email' => 'gm-one@example.com',
            'can_access_gm' => true,
            'password' => 'secret-pass',
        ]);

        $game = Game::query()->create([
            'title' => 'Моя игра',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        $csrfToken = $this->authenticate($gameMaster);

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->patchJson('/api/games/'.$game->id.'/status', [
            'status' => 'active',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'active');

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'status' => 'active',
        ]);
    }

    /**
     * Проверяет, что мастер может отправить приглашение игроку в свою игру.
     */
    public function test_game_master_can_invite_member_to_game(): void
    {
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
            'title' => 'Моя игра',
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

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/games/'.$game->id.'/invitations', [
            'login' => 'player@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('members_count', 1)
            ->assertJsonPath('invitations.0.invited_user.email', 'player@example.com');

        $this->assertDatabaseHas('game_invitations', [
            'game_id' => $game->id,
            'invited_user_id' => $player->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('game_members', [
            'game_id' => $game->id,
            'user_id' => $player->id,
        ]);
    }

    /**
     * Проверяет, что игрок попадает в игру только после принятия приглашения.
     */
    public function test_player_can_accept_game_invitation(): void
    {
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
            'title' => 'Моя игра',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        GameMember::query()->create([
            'game_id' => $game->id,
            'user_id' => $gameMaster->id,
            'role' => 'gm',
            'status' => 'active',
        ]);

        $invitation = GameInvitation::query()->create([
            'game_id' => $game->id,
            'gm_user_id' => $gameMaster->id,
            'invited_user_id' => $player->id,
            'token' => 'invite-token-1',
            'status' => 'pending',
            'sent_at' => now(),
        ]);

        $csrfToken = $this->authenticate($player);

        $this->getJson('/api/game-invitations', $this->frontendHeaders())
            ->assertOk()
            ->assertJsonPath('0.token', $invitation->token);

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/game-invitations/'.$invitation->token.'/accept')
            ->assertOk()
            ->assertJsonPath('status', 'accepted')
            ->assertJsonPath('game.id', $game->id);

        $this->assertDatabaseHas('game_members', [
            'game_id' => $game->id,
            'user_id' => $player->id,
            'role' => 'player',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('game_invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);
    }

    /**
     * Проверяет, что игрок может отклонить приглашение без вступления в игру.
     */
    public function test_player_can_decline_game_invitation(): void
    {
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
            'title' => 'Моя игра',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        GameMember::query()->create([
            'game_id' => $game->id,
            'user_id' => $gameMaster->id,
            'role' => 'gm',
            'status' => 'active',
        ]);

        $invitation = GameInvitation::query()->create([
            'game_id' => $game->id,
            'gm_user_id' => $gameMaster->id,
            'invited_user_id' => $player->id,
            'token' => 'invite-token-2',
            'status' => 'pending',
            'sent_at' => now(),
        ]);

        $csrfToken = $this->authenticate($player);

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->postJson('/api/game-invitations/'.$invitation->token.'/decline')
            ->assertOk()
            ->assertJsonPath('status', 'declined')
            ->assertJsonPath('game.id', $game->id);

        $this->assertDatabaseMissing('game_members', [
            'game_id' => $game->id,
            'user_id' => $player->id,
            'role' => 'player',
        ]);

        $this->assertDatabaseHas('game_invitations', [
            'id' => $invitation->id,
            'status' => 'declined',
        ]);
    }

    /**
     * Проверяет, что мастер может удалить участника из своей игры.
     */
    public function test_game_master_can_remove_member_from_game(): void
    {
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
            'title' => 'Моя игра',
            'gm_user_id' => $gameMaster->id,
            'status' => 'draft',
        ]);

        GameMember::query()->create([
            'game_id' => $game->id,
            'user_id' => $gameMaster->id,
            'role' => 'gm',
            'status' => 'active',
        ]);

        $member = GameMember::query()->create([
            'game_id' => $game->id,
            'user_id' => $player->id,
            'role' => 'player',
            'status' => 'active',
        ]);

        $csrfToken = $this->authenticate($gameMaster);

        $this->withHeaders([
            ...$this->frontendHeaders(),
            'X-CSRF-TOKEN' => $csrfToken,
        ])->deleteJson('/api/games/'.$game->id.'/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('members_count', 1);

        $this->assertDatabaseMissing('game_members', [
            'id' => $member->id,
        ]);
    }
}

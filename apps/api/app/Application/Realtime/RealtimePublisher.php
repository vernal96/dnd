<?php

declare(strict_types=1);

namespace App\Application\Realtime;

use App\Models\GameInvitation;
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
		$this->publish(
			event: 'game-invitation.created',
			targetUserIds: [$invitation->invited_user_id, $invitation->gm_user_id],
			invitation: $invitation,
		);
	}

	/**
	 * Публикует подготовленное realtime-сообщение в Redis.
	 *
	 * @param list<int> $targetUserIds
	 *
	 * @throws Throwable
	 */
	private function publish(string $event, array $targetUserIds, GameInvitation $invitation): void
	{
		$payload = json_encode([
			'event' => $event,
			'targetUserIds' => array_values(array_unique($targetUserIds)),
			'payload' => [
				'gameId' => $invitation->game_id,
				'gmUserId' => $invitation->gm_user_id,
				'invitationId' => $invitation->id,
				'invitedUserId' => $invitation->invited_user_id,
				'status' => $invitation->status,
				'token' => $invitation->token,
			],
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
		$this->publish(
			event: 'game-invitation.accepted',
			targetUserIds: [$invitation->invited_user_id, $invitation->gm_user_id],
			invitation: $invitation,
		);
	}

	/**
	 * Публикует событие отклонения приглашения в игру.
	 *
	 * @throws Throwable Если сериализация payload завершилась ошибкой.
	 */
	public function publishInvitationDeclined(GameInvitation $invitation): void
	{
		$this->publish(
			event: 'game-invitation.declined',
			targetUserIds: [$invitation->invited_user_id, $invitation->gm_user_id],
			invitation: $invitation,
		);
	}
}

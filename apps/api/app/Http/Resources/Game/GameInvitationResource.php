<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Models\GameInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Преобразует приглашение в игру в JSON.
 *
 * @mixin GameInvitation
 */
final class GameInvitationResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		/** @var GameInvitation $invitation */
		$invitation = $this->resource;

		return [
			'id' => $invitation->id,
			'game_id' => $invitation->game_id,
			'gm_user_id' => $invitation->gm_user_id,
			'invited_user_id' => $invitation->invited_user_id,
			'token' => $invitation->token,
			'status' => $invitation->status,
			'sent_at' => $invitation->sent_at?->toJSON(),
			'responded_at' => $invitation->responded_at?->toJSON(),
			'game' => $this->whenLoaded(
				'game',
				fn (): array => GameResource::make($invitation->game)->resolve($request),
			),
			'gm' => $this->whenLoaded(
				'gm',
				fn (): array => UserSummaryResource::make($invitation->gm)->resolve($request),
			),
			'invited_user' => $this->whenLoaded(
				'invitedUser',
				fn (): array => UserSummaryResource::make($invitation->invitedUser)->resolve($request),
			),
		];
	}
}

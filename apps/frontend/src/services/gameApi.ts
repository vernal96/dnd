import type {
  CreateGamePayload,
  GameDetail,
  GameInvitationSummary,
  PlayerActiveGameSummary,
  GameStatus,
  GameStatusFilter,
  GameSummary,
  InviteGameMemberPayload,
  PaginatedGamesResponse,
} from '@/types/game';
import { fetchWithSession } from '@/services/httpApi';
import type { PlayerCharacter } from '@/types/playerCharacter';

/**
 * Возвращает список игр текущего мастера.
 */
export function fetchGames(status: GameStatusFilter): Promise<PaginatedGamesResponse> {
  const queryString = status === 'all' ? '' : `?status=${status}`;

  return fetchWithSession<PaginatedGamesResponse>(`/games${queryString}`);
}

/**
 * Создает новую игру в кабинете мастера.
 */
export function createGame(payload: CreateGamePayload): Promise<GameSummary> {
  return fetchWithSession<GameSummary>('/games', {
    method: 'POST',
    body: JSON.stringify({
      title: payload.title,
      description: payload.description || null,
    }),
  });
}

/**
 * Возвращает одну игру текущего мастера.
 */
export function fetchGame(gameId: number): Promise<GameDetail> {
  return fetchWithSession<GameDetail>(`/games/${gameId}`);
}

/**
 * Обновляет статус одной игры.
 */
export function updateGameStatus(gameId: number, status: GameStatus): Promise<GameDetail> {
  return fetchWithSession<GameDetail>(`/games/${gameId}/status`, {
    method: 'PATCH',
    body: JSON.stringify({
      status,
    }),
  });
}

/**
 * Отправляет приглашение участнику в игру текущего мастера.
 */
export function inviteGameMember(gameId: number, payload: InviteGameMemberPayload): Promise<GameDetail> {
  return fetchWithSession<GameDetail>(`/games/${gameId}/invitations`, {
    method: 'POST',
    body: JSON.stringify({
      login: payload.login,
    }),
  });
}

/**
 * Удаляет участника из игры текущего мастера.
 */
export function removeGameMember(gameId: number, memberId: number): Promise<GameDetail> {
  return fetchWithSession<GameDetail>(`/games/${gameId}/members/${memberId}`, {
    method: 'DELETE',
  });
}

/**
 * Возвращает список приглашений текущего игрока.
 */
export function fetchPlayerInvitations(): Promise<GameInvitationSummary[]> {
  return fetchWithSession<GameInvitationSummary[]>('/game-invitations');
}

/**
 * Возвращает доступных персонажей для принятия приглашения.
 */
export function fetchInvitationAvailableCharacters(token: string): Promise<PlayerCharacter[]> {
  return fetchWithSession<PlayerCharacter[]>(`/game-invitations/${token}/characters`);
}

/**
 * Принимает одно приглашение в игру.
 */
export function acceptPlayerInvitation(token: string, characterId: number): Promise<GameInvitationSummary> {
  return fetchWithSession<GameInvitationSummary>(`/game-invitations/${token}/accept`, {
    method: 'POST',
    body: JSON.stringify({
      character_id: characterId,
    }),
  });
}

/**
 * Отклоняет одно приглашение в игру.
 */
export function declinePlayerInvitation(token: string): Promise<GameInvitationSummary> {
  return fetchWithSession<GameInvitationSummary>(`/game-invitations/${token}/decline`, {
    method: 'POST',
  });
}

/**
 * Возвращает активные игры текущего игрока, где уже запущена сцена.
 */
export function fetchPlayerActiveGames(): Promise<PlayerActiveGameSummary[]> {
  return fetchWithSession<PlayerActiveGameSummary[]>('/player/games/active');
}

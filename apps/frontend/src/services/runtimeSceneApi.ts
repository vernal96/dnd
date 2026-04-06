import { fetchWithSession } from '@/services/httpApi';
import type { RuntimeActorInstance, RuntimeActorMovePayload, RuntimeSceneDetail } from '@/types/runtimeScene';

/**
 * Возвращает активную runtime-сцену игры текущего мастера.
 */
export function fetchActiveRuntimeScene(gameId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/scene`);
}

/**
 * Возвращает активную runtime-сцену игры, доступную текущему игроку.
 */
export function fetchPlayerActiveRuntimeScene(gameId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/player/games/${gameId}/runtime/scene`);
}

/**
 * Перемещает героя текущего игрока по активной сцене.
 */
export function movePlayerRuntimeActor(gameId: number, actorId: number, payload: RuntimeActorMovePayload): Promise<RuntimeActorInstance> {
  return fetchWithSession<RuntimeActorInstance>(`/player/games/${gameId}/runtime/actors/${actorId}/move`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

/**
 * Расходует основное действие текущего героя игрока в encounter.
 */
export function usePlayerRuntimeAction(gameId: number, actorId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/player/games/${gameId}/runtime/actors/${actorId}/action`, {
    method: 'POST',
  });
}

/**
 * Расходует дополнительное действие текущего героя игрока в encounter.
 */
export function usePlayerRuntimeBonusAction(gameId: number, actorId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/player/games/${gameId}/runtime/actors/${actorId}/bonus-action`, {
    method: 'POST',
  });
}

/**
 * Завершает текущий ход героя игрока в encounter.
 */
export function endPlayerRuntimeTurn(gameId: number, actorId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/player/games/${gameId}/runtime/actors/${actorId}/end-turn`, {
    method: 'POST',
  });
}

/**
 * Активирует authored-сцену как runtime-сцену.
 */
export function activateRuntimeScene(gameId: number, sceneStateId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/scenes/${sceneStateId}/activate`, {
    method: 'POST',
  });
}

/**
 * Перемещает runtime-актора по текущей сцене.
 */
export function moveRuntimeActor(gameId: number, actorId: number, payload: RuntimeActorMovePayload): Promise<RuntimeActorInstance> {
  return fetchWithSession<RuntimeActorInstance>(`/games/${gameId}/runtime/actors/${actorId}/move`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

/**
 * Запускает encounter на активной runtime-сцене.
 */
export function startRuntimeEncounter(gameId: number, payload: { actor_ids: number[] }): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/encounter/start`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

/**
 * Расходует основное действие текущего участника encounter.
 */
export function useRuntimeAction(gameId: number, actorId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/actors/${actorId}/action`, {
    method: 'POST',
  });
}

/**
 * Расходует дополнительное действие текущего участника encounter.
 */
export function useRuntimeBonusAction(gameId: number, actorId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/actors/${actorId}/bonus-action`, {
    method: 'POST',
  });
}

/**
 * Переводит encounter на следующий ход.
 */
export function endRuntimeTurn(gameId: number): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/encounter/end-turn`, {
    method: 'POST',
  });
}

/**
 * Добавляет героя или NPC на активную сцену.
 */
export function spawnRuntimeActor(
  gameId: number,
  payload: { source_id: number; source_type: 'npc' | 'player_character'; x: number; y: number },
): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/actors/spawn`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

/**
 * Изменяет поверхность клетки на активной сцене.
 */
export function paintRuntimeCell(
  gameId: number,
  payload: { terrain_type: string; x: number; y: number },
): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/cells/paint`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

/**
 * Размещает предмет на активной сцене.
 */
export function dropRuntimeItem(
  gameId: number,
  payload: { item_code: string; quantity: number; x: number; y: number },
): Promise<RuntimeSceneDetail> {
  return fetchWithSession<RuntimeSceneDetail>(`/games/${gameId}/runtime/items/drop`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

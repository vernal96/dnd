import {fetchWithSession} from '@/services/httpApi';
import type {GameActor, SaveGameActorPayload} from '@/types/actor';

/**
 * Возвращает библиотеку актеров текущего мастера.
 */
export function fetchGameActors(): Promise<GameActor[]> {
    return fetchWithSession<GameActor[]>('/gm/actors');
}

/**
 * Создает NPC в библиотеке текущего мастера.
 */
export function createGameActor(payload: SaveGameActorPayload): Promise<GameActor> {
    return fetchWithSession<GameActor>('/gm/actors', {
        method: 'POST',
        body: JSON.stringify(payload),
    });
}

/**
 * Полностью обновляет NPC текущего мастера.
 */
export function updateGameActor(actorId: number, payload: SaveGameActorPayload): Promise<GameActor> {
    return fetchWithSession<GameActor>(`/gm/actors/${actorId}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
    });
}

/**
 * Удаляет NPC из библиотеки текущего мастера.
 */
export function deleteGameActor(actorId: number): Promise<void> {
    return fetchWithSession<void>(`/gm/actors/${actorId}`, {
        method: 'DELETE',
    });
}

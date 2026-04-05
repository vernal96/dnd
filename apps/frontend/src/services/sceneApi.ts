import {fetchWithSession} from '@/services/httpApi';
import type {CreateScenePayload, GameSceneDetail, UpdateScenePayload} from '@/types/scene';

/**
 * Создает новую сцену внутри выбранной игры.
 */
export function createGameScene(gameId: number, payload: CreateScenePayload): Promise<GameSceneDetail> {
    return fetchWithSession<GameSceneDetail>(`/games/${gameId}/scenes`, {
        method: 'POST',
        body: JSON.stringify(payload),
    });
}

/**
 * Возвращает одну сцену для редактора мастера.
 */
export function fetchGameScene(gameId: number, sceneId: number): Promise<GameSceneDetail> {
    return fetchWithSession<GameSceneDetail>(`/games/${gameId}/scenes/${sceneId}`);
}

/**
 * Сохраняет authored-сцену целиком.
 */
export function updateGameScene(gameId: number, sceneId: number, payload: UpdateScenePayload): Promise<GameSceneDetail> {
    return fetchWithSession<GameSceneDetail>(`/games/${gameId}/scenes/${sceneId}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
    });
}

/**
 * Удаляет authored-сцену из игры.
 */
export function deleteGameScene(gameId: number, sceneId: number): Promise<void> {
    return fetchWithSession<void>(`/games/${gameId}/scenes/${sceneId}`, {
        method: 'DELETE',
    });
}

import {fetchWithSession} from '@/services/httpApi';
import type {GameImageAsset} from '@/types/media';

/**
 * Возвращает список изображений выбранной игры.
 */
export function fetchGameImages(gameId: number): Promise<GameImageAsset[]> {
    return fetchWithSession<GameImageAsset[]>(`/games/${gameId}/images`);
}

/**
 * Загружает новое изображение в хранилище игры.
 */
export function uploadGameImage(gameId: number, file: File): Promise<GameImageAsset> {
    const formData = new FormData();
    formData.append('file', file);

    return fetchWithSession<GameImageAsset>(`/games/${gameId}/images`, {
        method: 'POST',
        body: formData,
    });
}

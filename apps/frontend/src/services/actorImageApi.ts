import {fetchWithSession} from '@/services/httpApi';
import type {GameImageAsset} from '@/types/media';

/**
 * Возвращает список изображений библиотеки NPC текущего мастера.
 */
export function fetchActorImages(): Promise<GameImageAsset[]> {
    return fetchWithSession<GameImageAsset[]>('/gm/actor-images');
}

/**
 * Загружает новое изображение в библиотеку NPC текущего мастера.
 */
export function uploadActorImage(file: File): Promise<GameImageAsset> {
    const formData = new FormData();
    formData.append('file', file);

    return fetchWithSession<GameImageAsset>('/gm/actor-images', {
        method: 'POST',
        body: formData,
    });
}

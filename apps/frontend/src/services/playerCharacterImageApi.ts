import {fetchWithSession} from '@/services/httpApi';
import type {GameImageAsset} from '@/types/media';

export type PlayerCharacterImageAsset = GameImageAsset & {
    storagePath: string;
};

/**
 * Загружает изображение персонажа текущего игрока.
 */
export function uploadPlayerCharacterImage(file: File): Promise<PlayerCharacterImageAsset> {
    const formData = new FormData();
    formData.append('file', file);

    return fetchWithSession<PlayerCharacterImageAsset>('/player/character-images', {
        method: 'POST',
        body: formData,
    });
}

import {fetchWithSession} from '@/services/httpApi';
import type {CreatePlayerCharacterPayload, PlayerCharacter} from '@/types/playerCharacter';

/**
 * Возвращает список персонажей текущего игрока.
 */
export function fetchPlayerCharacters(): Promise<PlayerCharacter[]> {
    return fetchWithSession<PlayerCharacter[]>('/player/characters');
}

/**
 * Создает нового персонажа текущего игрока.
 */
export function createPlayerCharacter(payload: CreatePlayerCharacterPayload): Promise<PlayerCharacter> {
    return fetchWithSession<PlayerCharacter>('/player/characters', {
        method: 'POST',
        body: JSON.stringify(payload),
    });
}

/**
 * Обновляет фото существующего персонажа игрока.
 */
export function updatePlayerCharacterImage(characterId: number, imagePath: string): Promise<PlayerCharacter> {
    return fetchWithSession<PlayerCharacter>(`/player/characters/${characterId}/image`, {
        method: 'PATCH',
        body: JSON.stringify({
            image_path: imagePath,
        }),
    });
}

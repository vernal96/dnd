import {fetchWithSession} from '@/services/httpApi';
import type {CharacterAbilityDefinition} from '@/types/catalog';

/**
 * Возвращает список базовых характеристик персонажа.
 */
export function fetchCharacterAbilities(): Promise<CharacterAbilityDefinition[]> {
    return fetchWithSession<CharacterAbilityDefinition[]>('/character-abilities');
}

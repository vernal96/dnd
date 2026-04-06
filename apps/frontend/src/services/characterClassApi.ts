import {fetchWithSession} from '@/services/httpApi';
import type {CharacterAbilityDefinition, CharacterClassDefinition} from '@/types/catalog';

/**
 * Возвращает список классов персонажей.
 */
export async function fetchCharacterClasses(): Promise<CharacterClassDefinition[]> {
    const classes = await fetchWithSession<Array<CharacterClassDefinition & Record<string, unknown>>>('/actor-catalog/classes');

    return classes.map((characterClass) => ({
        abilityBonuses: characterClass.abilityBonuses as Record<string, number>,
        code: characterClass.code,
        defaultPointBuyAllocation: characterClass.defaultPointBuyAllocation as Record<string, number>,
        description: characterClass.description,
        isActive: characterClass.isActive,
        isPlayerSelectable: Boolean(characterClass.isPlayerSelectable),
        name: characterClass.name,
        primaryAbilities: Array.isArray(characterClass.primaryAbilities) ? characterClass.primaryAbilities as CharacterAbilityDefinition[] : [],
    }));
}

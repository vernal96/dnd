import {fetchWithSession} from '@/services/httpApi';
import type {RaceDefinition} from '@/types/catalog';

/**
 * Возвращает список рас и подрас.
 */
export function fetchRaces(): Promise<RaceDefinition[]> {
    return fetchWithSession<RaceDefinition[]>('/races');
}

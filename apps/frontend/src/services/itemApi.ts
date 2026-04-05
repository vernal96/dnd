import {fetchWithSession} from '@/services/httpApi';
import type {CatalogItem} from '@/types/item';

/**
 * Возвращает кодовый каталог предметов.
 */
export function fetchItems(): Promise<CatalogItem[]> {
    return fetchWithSession<CatalogItem[]>('/items');
}

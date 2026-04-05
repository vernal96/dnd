import {fetchWithSession} from '@/services/httpApi';
import type {SceneObjectDefinition, SceneSurfaceDefinition} from '@/types/scene';

/**
 * Возвращает серверный каталог поверхностей сцены.
 */
export function fetchSceneSurfaces(): Promise<SceneSurfaceDefinition[]> {
    return fetchWithSession<SceneSurfaceDefinition[]>('/scene-catalog/surfaces');
}

/**
 * Возвращает серверный каталог объектов сцены.
 */
export function fetchSceneObjects(): Promise<SceneObjectDefinition[]> {
    return fetchWithSession<SceneObjectDefinition[]>('/scene-catalog/objects');
}

<script setup lang="ts">
import { ArrowLeft, Minus, Plus, Save, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import SceneObjectInventoryModal from '@/components/runtime/SceneObjectInventoryModal.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import { useToastCenter } from '@/composables/useToastCenter';
import { fetchGameActors } from '@/services/actorApi';
import { fetchItems } from '@/services/itemApi';
import { fetchSceneObjects, fetchSceneSurfaces } from '@/services/sceneCatalogApi';
import { fetchGameScene, updateGameScene } from '@/services/sceneApi';
import type { GameActor } from '@/types/actor';
import type { CatalogItem } from '@/types/item';
import type { RuntimeActorInventoryItem } from '@/types/runtimeScene';
import type { SceneActorPlacement, SceneCell, SceneObject, SceneObjectDefinition, ScenePlayerSpawnPoint, SceneSurfaceDefinition, SceneViewportMetadata } from '@/types/scene';
import { resolveCharacterClassLabel, resolveRaceLabel } from '@/utils/catalogLabel';

type GridResizeEdge = 'bottom' | 'left' | 'right' | 'top';
type GridResizeMode = 'expand' | 'shrink';
type PointerMode = 'pan' | 'rotate' | null;
type CanvasPoint = {
  x: number;
  y: number;
};
type ProjectedCell = {
  bounds: {
    maxX: number;
    maxY: number;
    minX: number;
    minY: number;
  };
  cell: SceneCell;
  center: CanvasPoint;
  corners: CanvasPoint[];
};
type ProjectedObjectFootprint = {
  bounds: {
    maxX: number;
    maxY: number;
    minX: number;
    minY: number;
  };
  center: CanvasPoint;
  corners: CanvasPoint[];
  object: SceneObject & { x: number; y: number };
};
type HoverActorTooltip = {
  name: string;
  x: number;
  y: number;
};

const TILE_WORLD_SIZE = 112;
const ELEVATION_STEP = 12;
const MIN_CANVAS_HEIGHT = 520;
const CANVAS_BACKGROUND = '#0a1120';

const route = useRoute();
const router = useRouter();
const { currentUser, ensureSessionLoaded, isAuthenticated, isPending } = useAuthSession();
const { pushToast } = useToastCenter();

const sceneError = ref('');
const isSceneLoading = ref(false);
const isSceneSaving = ref(false);
const sceneName = ref('');
const sceneDescription = ref('');
const gridWidth = ref(6);
const gridHeight = ref(6);
const sceneCells = ref<SceneCell[]>([]);
const sceneObjects = ref<SceneObject[]>([]);
const sceneActorPlacements = ref<SceneActorPlacement[]>([]);
const surfaceCatalog = ref<SceneSurfaceDefinition[]>([]);
const objectCatalog = ref<SceneObjectDefinition[]>([]);
const itemCatalog = ref<CatalogItem[]>([]);
const gameActors = ref<GameActor[]>([]);
const activeTerrain = ref<SceneSurfaceDefinition['code']>('grass');
const activeEraseMode = ref(false);
const playerSpawnPoint = ref<ScenePlayerSpawnPoint | null>(null);
const editorContextMenu = ref<{ cellX: number; cellY: number; x: number; y: number } | null>(null);
const surfacePickerCell = ref<{ x: number; y: number } | null>(null);
const objectPickerCell = ref<{ x: number; y: number } | null>(null);
const actorPickerCell = ref<{ x: number; y: number } | null>(null);
const objectInventoryTarget = ref<SceneObject | null>(null);
const viewport = ref<SceneViewportMetadata>({
  offsetX: 0,
  offsetY: 0,
  rotateX: 45,
  rotateZ: 45,
  zoom: 1,
});

const canvasViewportRef = ref<HTMLDivElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const hoveredCellKey = ref<string | null>(null);
const selectedCellKey = ref<string | null>(null);
const hoveredActorTooltip = ref<HoverActorTooltip | null>(null);
const canvasSize = ref({
  height: MIN_CANVAS_HEIGHT,
  width: 1200,
});

const pointerMode = ref<PointerMode>(null);
const pointerStartX = ref(0);
const pointerStartY = ref(0);
const pointerStartOffsetX = ref(0);
const pointerStartOffsetY = ref(0);
const pointerStartRotateX = ref(45);
const pointerStartRotateZ = ref(-45);
const hasMoved = ref(false);
const isPaintDragging = ref(false);

const renderFrameId = ref<number | null>(null);
let resizeObserver: ResizeObserver | null = null;

const imageCache = new Map<string, HTMLImageElement>();
const brokenImageUrls = new Set<string>();
const loadingImageUrls = new Set<string>();

const gameId = computed<number | null>(() => parseRouteParam(route.params.id));
const sceneId = computed<number | null>(() => parseRouteParam(route.params.sceneId));
const sceneBackLink = computed<string>(() => (gameId.value === null ? '/cabinet/gm' : `/cabinet/gm/games/${gameId.value}`));
const selectedCell = computed<SceneCell | null>(() => {
  if (selectedCellKey.value === null) {
    return null;
  }

  const [xRaw, yRaw] = selectedCellKey.value.split('-');
  const x = Number.parseInt(xRaw ?? '', 10);
  const y = Number.parseInt(yRaw ?? '', 10);

  if (Number.isNaN(x) || Number.isNaN(y)) {
    return null;
  }

  return getCell(x, y) ?? null;
});
const selectedSceneObject = computed<SceneObject | null>(() => {
  if (selectedCell.value === null) {
    return null;
  }

  return getObjectAtCell(selectedCell.value.x, selectedCell.value.y) ?? null;
});
const selectedActorPlacement = computed<SceneActorPlacement | null>(() => {
  if (selectedCell.value === null) {
    return null;
  }

  return getActorPlacementAtCell(selectedCell.value.x, selectedCell.value.y) ?? null;
});

/**
 * Извлекает числовой параметр маршрута.
 */
function parseRouteParam(rawValue: unknown): number | null {
  const parsedValue = Number.parseInt(Array.isArray(rawValue) ? rawValue[0] : String(rawValue), 10);

  return Number.isNaN(parsedValue) ? null : parsedValue;
}

/**
 * Возвращает authored-клетку по координатам.
 */
function getCell(x: number, y: number): SceneCell | undefined {
  return sceneCells.value.find((cell) => cell.x === x && cell.y === y);
}

/**
 * Возвращает объект сцены по координатам клетки.
 */
function getObjectAtCell(x: number, y: number): SceneObject | undefined {
  return sceneObjects.value.find((object) => object.x !== null && object.y !== null && objectOccupiesCell(object, x, y));
}

/**
 * Возвращает true, если объект занимает указанную authored-клетку.
 */
function objectOccupiesCell(object: SceneObject, x: number, y: number): boolean {
  if (object.x === null || object.y === null) {
    return false;
  }

  return x >= object.x
    && x < object.x + Math.max(1, object.width)
    && y >= object.y
    && y < object.y + Math.max(1, object.height);
}

/**
 * Возвращает true, если footprints двух authored-объектов пересекаются.
 */
function doObjectFootprintsIntersect(left: SceneObject, right: SceneObject): boolean {
  if (left.x === null || left.y === null || right.x === null || right.y === null) {
    return false;
  }

  return left.x < right.x + right.width
    && left.x + left.width > right.x
    && left.y < right.y + right.height
    && left.y + left.height > right.y;
}

/**
 * Возвращает размещение актора по координатам клетки.
 */
function getActorPlacementAtCell(x: number, y: number): SceneActorPlacement | undefined {
  return sceneActorPlacements.value.find((placement) => placement.x === x && placement.y === y);
}

/**
 * Создает полную прямоугольную сетку authored-клеток.
 */
function buildGridCells(width: number, height: number, source: SceneCell[]): SceneCell[] {
  const nextCells: SceneCell[] = [];

  for (let y = 0; y < height; y += 1) {
    for (let x = 0; x < width; x += 1) {
      const existingCell = source.find((cell) => cell.x === x && cell.y === y);

      nextCells.push({
        x,
        y,
        terrain_type: existingCell?.terrain_type ?? 'grass',
        elevation: existingCell?.elevation ?? 0,
        is_passable: existingCell?.is_passable ?? true,
        blocks_vision: existingCell?.blocks_vision ?? false,
        props: existingCell?.props ?? null,
      });
    }
  }

  return nextCells;
}

/**
 * Применяет выбранную поверхность к authored-клетке.
 */
function paintCell(x: number, y: number): void {
  const cell = getCell(x, y);
  const surface = surfaceCatalog.value.find((item) => item.code === activeTerrain.value);

  if (!cell || !surface) {
    return;
  }

  cell.terrain_type = surface.code;
  cell.is_passable = surface.is_passable;
  cell.blocks_vision = surface.blocks_vision;
}

/**
 * Удаляет объект и NPC с выбранной клетки.
 */
function eraseCellContent(x: number, y: number): void {
  sceneObjects.value = sceneObjects.value.filter((object) => !objectOccupiesCell(object, x, y));
  sceneActorPlacements.value = sceneActorPlacements.value.filter((placement) => !(placement.x === x && placement.y === y));
}

/**
 * Переключает authored-объект на клетке.
 */
function toggleObjectAtCell(x: number, y: number, objectKind: SceneObjectDefinition['code']): void {
  const existingObject = getObjectAtCell(x, y);

  if (existingObject && existingObject.kind === objectKind) {
    sceneObjects.value = sceneObjects.value.filter((object) => object !== existingObject);

    return;
  }

  const objectDefinition = objectCatalog.value.find((item) => item.code === objectKind);

  if (!objectDefinition) {
    return;
  }

  const nextObject: SceneObject = {
    kind: objectDefinition.code,
    name: objectDefinition.name,
    x,
    y,
    width: objectDefinition.width,
    height: objectDefinition.height,
    is_hidden: false,
    is_interactive: objectDefinition.is_interactive,
    state: objectDefinition.code === 'house'
      ? {
          inventory: [],
        }
      : null,
  };

  sceneObjects.value = [
    ...sceneObjects.value.filter((object) => !doObjectFootprintsIntersect(object, nextObject)),
    nextObject,
  ];
}

/**
 * Переключает authored-размещение актора на клетке.
 */
function toggleActorAtCell(x: number, y: number, actorId: number): void {
  const existingPlacement = getActorPlacementAtCell(x, y);

  if (existingPlacement && existingPlacement.actor_id === actorId) {
    sceneActorPlacements.value = sceneActorPlacements.value.filter((placement) => !(placement.x === x && placement.y === y));

    return;
  }

  const actor = gameActors.value.find((item) => item.id === actorId);

  if (!actor) {
    return;
  }

  sceneActorPlacements.value = [
    ...sceneActorPlacements.value.filter((placement) => placement.actor_id !== actor.id && !(placement.x === x && placement.y === y)),
    {
      actor,
      actor_id: actor.id,
      x,
      y,
    },
  ];
}

/**
 * Переносит authored-точку спауна игроков на выбранную клетку.
 */
function setPlayerSpawnAtCell(x: number, y: number): void {
  playerSpawnPoint.value = playerSpawnPoint.value?.x === x && playerSpawnPoint.value?.y === y
    ? null
    : { x, y };
}

/**
 * Возвращает CSS-класс превью поверхности.
 */
function resolveSurfacePreviewClass(code: SceneSurfaceDefinition['code']): string {
  return `terrain-preview-${code}`;
}

/**
 * Возвращает CSS-класс превью объекта.
 */
function resolveObjectPreviewClass(code: SceneObjectDefinition['code']): string {
  return `scene-object-preview-${code}`;
}

/**
 * Возвращает загруженное изображение authored-объекта.
 */
function resolveSceneObjectImage(code: SceneObjectDefinition['code']): HTMLImageElement | null {
  const objectDefinition = objectCatalog.value.find((item) => item.code === code);

  return resolveCachedImage(objectDefinition?.image_url ?? null);
}

/**
 * Возвращает цвет поверхности для отрисовки на canvas.
 */
function resolveSurfacePalette(code: string): {
  accent: string;
  fill: string;
  shadow: string;
  stroke: string;
} {
  switch (code) {
    case 'stone':
      return { accent: '#d7dde8', fill: '#718196', shadow: '#334155', stroke: '#c8d0dc' };
    case 'soil':
      return { accent: '#f8d2b1', fill: '#82552f', shadow: '#4a2c16', stroke: '#d1a782' };
    case 'water':
      return { accent: '#b6f0ff', fill: '#0d6685', shadow: '#0a2f4a', stroke: '#7dd3fc' };
    case 'fire':
      return { accent: '#fde68a', fill: '#b43714', shadow: '#6d1d13', stroke: '#fdba74' };
    case 'poison':
      return { accent: '#e8fca8', fill: '#5b8f16', shadow: '#30460e', stroke: '#d9f99d' };
    case 'ice':
      return { accent: '#f0f9ff', fill: '#7dbbf9', shadow: '#285aaf', stroke: '#dbeafe' };
    case 'grass':
    default:
      return { accent: '#d9f99d', fill: '#3d7d43', shadow: '#1f4b2f', stroke: '#bbf7d0' };
  }
}

/**
 * Возвращает загруженное изображение поверхности.
 */
function resolveSurfaceTexture(code: SceneSurfaceDefinition['code']): HTMLImageElement | null {
  const surfaceDefinition = surfaceCatalog.value.find((item) => item.code === code);

  return resolveCachedImage(surfaceDefinition?.image_url ?? null);
}

/**
 * Загружает изображение по URL и кэширует его для canvas-рендера.
 */
function resolveCachedImage(url: string | null | undefined): HTMLImageElement | null {
  if (!url || brokenImageUrls.has(url)) {
    return null;
  }

  const cachedImage = imageCache.get(url);

  if (cachedImage && cachedImage.complete && cachedImage.naturalWidth > 0) {
    return cachedImage;
  }

  if (!loadingImageUrls.has(url)) {
    loadingImageUrls.add(url);
    const image = new Image();
    image.onload = () => {
      imageCache.set(url, image);
      loadingImageUrls.delete(url);
      scheduleCanvasRender();
    };
    image.onerror = () => {
      loadingImageUrls.delete(url);
      brokenImageUrls.add(url);
      imageCache.delete(url);
      scheduleCanvasRender();
    };
    image.src = url;
  }

  return null;
}

/**
 * Загружает authored-сцену и серверные каталоги в редактор.
 */
async function loadScene(): Promise<void> {
  if (gameId.value === null || sceneId.value === null) {
    sceneError.value = 'Сцена не найдена.';

    return;
  }

  isSceneLoading.value = true;
  sceneError.value = '';

  try {
    const [scene, surfaces, objects, actors, items] = await Promise.all([
      fetchGameScene(gameId.value, sceneId.value),
      fetchSceneSurfaces(),
      fetchSceneObjects(),
      fetchGameActors(),
      fetchItems(),
    ]);

    surfaceCatalog.value = surfaces;
    objectCatalog.value = objects;
    gameActors.value = actors.filter((actor) => actor.kind === 'npc');
    itemCatalog.value = items;
    sceneName.value = scene.scene_template.name;
    sceneDescription.value = scene.scene_template.description ?? '';
    gridWidth.value = Math.max(6, scene.scene_template.width);
    gridHeight.value = Math.max(6, scene.scene_template.height);
    sceneCells.value = buildGridCells(gridWidth.value, gridHeight.value, scene.scene_template.cells);
    sceneObjects.value = scene.scene_template.objects.filter((object): object is SceneObject => object.x !== null && object.y !== null);
    sceneActorPlacements.value = scene.scene_template.actor_placements;
    playerSpawnPoint.value = scene.scene_template.metadata?.player_spawn_point ?? null;

    const savedViewport = scene.scene_template.metadata?.viewport;
    viewport.value = {
      offsetX: typeof savedViewport?.offsetX === 'number' ? savedViewport.offsetX : 0,
      offsetY: typeof savedViewport?.offsetY === 'number' ? savedViewport.offsetY : 0,
      rotateX: typeof savedViewport?.rotateX === 'number' ? savedViewport.rotateX : 45,
      rotateZ: typeof savedViewport?.rotateZ === 'number' ? savedViewport.rotateZ : 45,
      zoom: typeof savedViewport?.zoom === 'number' ? savedViewport.zoom : 1,
    };

    scheduleCanvasRender();
  } catch (error) {
    sceneError.value = (error as Error).message;
  } finally {
    isSceneLoading.value = false;
  }
}

/**
 * Сохраняет authored-сцену.
 */
async function handleSaveScene(): Promise<void> {
  if (gameId.value === null || sceneId.value === null) {
    return;
  }

  isSceneSaving.value = true;
  sceneError.value = '';

  try {
    await updateGameScene(gameId.value, sceneId.value, {
      name: sceneName.value,
      description: sceneDescription.value,
      width: gridWidth.value,
      height: gridHeight.value,
      metadata: {
        player_spawn_point: playerSpawnPoint.value,
        viewport: viewport.value,
      },
      cells: sceneCells.value,
      objects: sceneObjects.value,
      actors: sceneActorPlacements.value.map((placement) => ({
        actor_id: placement.actor_id,
        x: placement.x,
        y: placement.y,
      })),
    });
    pushToast('Сцена сохранена', 'Изменения сцены записаны на сервер.', 'success');
  } catch (error) {
    sceneError.value = (error as Error).message;
  } finally {
    isSceneSaving.value = false;
  }
}

/**
 * Применяет смещение координаты по выбранной стороне изменения сетки.
 */
function transformGridCoordinate(value: number, edge: GridResizeEdge, mode: GridResizeMode): number {
  if (mode === 'expand' && (edge === 'left' || edge === 'top')) {
    return value + 1;
  }

  if (mode === 'shrink' && (edge === 'left' || edge === 'top')) {
    return value - 1;
  }

  return value;
}

/**
 * Возвращает true, если координата остается внутри новых границ authored-сетки.
 */
function isInsideGridBounds(x: number, y: number, width: number, height: number): boolean {
  return x >= 0 && y >= 0 && x < width && y < height;
}

/**
 * Возвращает true, если footprint authored-объекта полностью помещается в authored-сетку.
 */
function isObjectInsideGridBounds(object: SceneObject, width: number, height: number): boolean {
  if (object.x === null || object.y === null) {
    return false;
  }

  return object.x >= 0
    && object.y >= 0
    && object.x + Math.max(1, object.width) <= width
    && object.y + Math.max(1, object.height) <= height;
}

/**
 * Обновляет ключ выбранной клетки после изменения размеров authored-сетки.
 */
function updateSelectedCellAfterGridResize(edge: GridResizeEdge, mode: GridResizeMode, nextWidth: number, nextHeight: number): void {
  if (selectedCellKey.value === null) {
    return;
  }

  const [xRaw, yRaw] = selectedCellKey.value.split('-');
  const currentX = Number.parseInt(xRaw ?? '', 10);
  const currentY = Number.parseInt(yRaw ?? '', 10);

  if (Number.isNaN(currentX) || Number.isNaN(currentY)) {
    selectedCellKey.value = null;
    return;
  }

  const nextX = transformGridCoordinate(currentX, edge, mode);
  const nextY = transformGridCoordinate(currentY, edge, mode);

  selectedCellKey.value = isInsideGridBounds(nextX, nextY, nextWidth, nextHeight)
    ? resolveCellKey(nextX, nextY)
    : null;
}

/**
 * Изменяет размеры authored-сетки со стороны, выбранной пользователем.
 */
function resizeGridFromEdge(edge: GridResizeEdge, mode: GridResizeMode): void {
  const nextWidth = edge === 'left' || edge === 'right'
    ? gridWidth.value + (mode === 'expand' ? 1 : -1)
    : gridWidth.value;
  const nextHeight = edge === 'top' || edge === 'bottom'
    ? gridHeight.value + (mode === 'expand' ? 1 : -1)
    : gridHeight.value;

  if (nextWidth < 6 || nextHeight < 6) {
    return;
  }

  const transformedCells = sceneCells.value
    .map((cell) => ({
      ...cell,
      x: transformGridCoordinate(cell.x, edge, mode),
      y: transformGridCoordinate(cell.y, edge, mode),
    }))
    .filter((cell) => isInsideGridBounds(cell.x, cell.y, nextWidth, nextHeight));

  sceneCells.value = buildGridCells(nextWidth, nextHeight, transformedCells);
  sceneObjects.value = sceneObjects.value
    .filter((object): object is SceneObject & { x: number; y: number } => object.x !== null && object.y !== null)
    .map((object) => ({
      ...object,
      x: transformGridCoordinate(object.x, edge, mode),
      y: transformGridCoordinate(object.y, edge, mode),
    }))
    .filter((object) => isObjectInsideGridBounds(object, nextWidth, nextHeight));
  sceneActorPlacements.value = sceneActorPlacements.value
    .map((placement) => ({
      ...placement,
      x: transformGridCoordinate(placement.x, edge, mode),
      y: transformGridCoordinate(placement.y, edge, mode),
    }))
    .filter((placement) => isInsideGridBounds(placement.x, placement.y, nextWidth, nextHeight));

  if (playerSpawnPoint.value !== null) {
    const nextSpawnX = transformGridCoordinate(playerSpawnPoint.value.x, edge, mode);
    const nextSpawnY = transformGridCoordinate(playerSpawnPoint.value.y, edge, mode);
    playerSpawnPoint.value = isInsideGridBounds(nextSpawnX, nextSpawnY, nextWidth, nextHeight)
      ? { x: nextSpawnX, y: nextSpawnY }
      : null;
  }

  gridWidth.value = nextWidth;
  gridHeight.value = nextHeight;
  updateSelectedCellAfterGridResize(edge, mode, nextWidth, nextHeight);
}

/**
 * Закрывает контекстное меню редактора.
 */
function closeEditorContextMenu(): void {
  editorContextMenu.value = null;
}

/**
 * Открывает выбор поверхности для клетки из контекстного меню.
 */
function openSurfacePickerForCell(x: number, y: number): void {
  surfacePickerCell.value = { x, y };
  closeEditorContextMenu();
}

/**
 * Открывает выбор authored-объекта для клетки из контекстного меню.
 */
function openObjectPickerForCell(x: number, y: number): void {
  objectPickerCell.value = { x, y };
  closeEditorContextMenu();
}

/**
 * Открывает выбор NPC для клетки из контекстного меню.
 */
function openActorPickerForCell(x: number, y: number): void {
  actorPickerCell.value = { x, y };
  closeEditorContextMenu();
}

/**
 * Применяет выбранную поверхность к клетке из модалки выбора.
 */
function applySurfaceSelection(surfaceCode: SceneSurfaceDefinition['code']): void {
  if (surfacePickerCell.value === null) {
    return;
  }

  activeTerrain.value = surfaceCode;
  paintCell(surfacePickerCell.value.x, surfacePickerCell.value.y);
  selectedCellKey.value = resolveCellKey(surfacePickerCell.value.x, surfacePickerCell.value.y);
  surfacePickerCell.value = null;
}

/**
 * Применяет выбранный authored-объект к клетке из модалки выбора.
 */
function applyObjectSelection(objectKind: SceneObjectDefinition['code']): void {
  if (objectPickerCell.value === null) {
    return;
  }

  toggleObjectAtCell(objectPickerCell.value.x, objectPickerCell.value.y, objectKind);
  selectedCellKey.value = resolveCellKey(objectPickerCell.value.x, objectPickerCell.value.y);
  objectPickerCell.value = null;
}

/**
 * Применяет выбранный NPC к клетке из модалки выбора.
 */
function applyActorSelection(actorId: number): void {
  if (actorPickerCell.value === null) {
    return;
  }

  toggleActorAtCell(actorPickerCell.value.x, actorPickerCell.value.y, actorId);
  selectedCellKey.value = resolveCellKey(actorPickerCell.value.x, actorPickerCell.value.y);
  actorPickerCell.value = null;
}

/**
 * Нормализует инвентарь authored-объекта к общему UI-контракту.
 */
function normalizeObjectInventory(inventory: unknown): RuntimeActorInventoryItem[] {
  if (!Array.isArray(inventory)) {
    return [];
  }

  return inventory
    .map((entry) => {
      if (typeof entry !== 'object' || entry === null) {
        return null;
      }

      const itemCode = typeof (entry as Record<string, unknown>).itemCode === 'string'
        ? (entry as Record<string, unknown>).itemCode as string
        : typeof (entry as Record<string, unknown>).item_code === 'string'
          ? (entry as Record<string, unknown>).item_code as string
          : null;

      if (itemCode === null) {
        return null;
      }

      return {
        isEquipped: Boolean((entry as Record<string, unknown>).isEquipped ?? (entry as Record<string, unknown>).is_equipped ?? false),
        itemCode,
        quantity: Number((entry as Record<string, unknown>).quantity ?? 1),
        slot: typeof (entry as Record<string, unknown>).slot === 'string' ? (entry as Record<string, unknown>).slot as string : null,
        state: typeof (entry as Record<string, unknown>).state === 'object' && (entry as Record<string, unknown>).state !== null
          ? (entry as Record<string, unknown>).state as Record<string, unknown>
          : null,
      };
    })
    .filter((entry): entry is RuntimeActorInventoryItem => entry !== null);
}

/**
 * Открывает инвентарь размещенного authored-объекта.
 */
function openObjectInventory(object: SceneObject): void {
  objectInventoryTarget.value = object;
  closeEditorContextMenu();
}

/**
 * Возвращает краткое состояние authored-клетки для меню и popup-окон.
 */
function describeCellState(x: number, y: number): string {
  const cell = getCell(x, y);
  const object = getObjectAtCell(x, y);
  const actorPlacement = getActorPlacementAtCell(x, y);
  const fragments = [
    cell?.terrain_type ?? 'unknown',
    object?.name ?? object?.kind ?? null,
    actorPlacement?.actor.name ?? null,
    playerSpawnPoint.value?.x === x && playerSpawnPoint.value?.y === y ? 'spawn' : null,
  ].filter((fragment): fragment is string => fragment !== null);

  return fragments.join(' · ');
}

/**
 * Закрывает меню редактора по внешнему клику.
 */
function handleGlobalPointerDown(): void {
  closeEditorContextMenu();
}

/**
 * Возвращает world-координату клетки.
 */
function resolveWorldCoordinate(value: number, dimension: number): number {
  return (value - (dimension / 2)) * TILE_WORLD_SIZE;
}

/**
 * Проецирует 3D-точку в экранные координаты canvas.
 */
function projectWorldPoint(x: number, y: number, z: number): CanvasPoint {
  const yaw = (viewport.value.rotateZ * Math.PI) / 180;
  const pitch = (viewport.value.rotateX * Math.PI) / 180;
  const cosYaw = Math.cos(yaw);
  const sinYaw = Math.sin(yaw);
  const cosPitch = Math.cos(pitch);
  const sinPitch = Math.sin(pitch);
  const rotatedX = (x * cosYaw) - (y * sinYaw);
  const rotatedY = (x * sinYaw) + (y * cosYaw);
  const projectedY = (rotatedY * cosPitch) - (z * sinPitch);
  const zoom = viewport.value.zoom;

  return {
    x: (canvasSize.value.width / 2) + viewport.value.offsetX + (rotatedX * zoom),
    y: (canvasSize.value.height / 2) + viewport.value.offsetY + (projectedY * zoom),
  };
}

/**
 * Собирает спроецированную геометрию одной authored-клетки.
 */
function projectCell(cell: SceneCell): ProjectedCell {
  const x0 = resolveWorldCoordinate(cell.x, gridWidth.value);
  const y0 = resolveWorldCoordinate(cell.y, gridHeight.value);
  const x1 = x0 + TILE_WORLD_SIZE;
  const y1 = y0 + TILE_WORLD_SIZE;
  const z = (cell.elevation ?? 0) * ELEVATION_STEP;
  const corners = [
    projectWorldPoint(x0, y0, z),
    projectWorldPoint(x1, y0, z),
    projectWorldPoint(x1, y1, z),
    projectWorldPoint(x0, y1, z),
  ];
  const center = projectWorldPoint(x0 + (TILE_WORLD_SIZE / 2), y0 + (TILE_WORLD_SIZE / 2), z);
  const xs = corners.map((corner) => corner.x);
  const ys = corners.map((corner) => corner.y);

  return {
    cell,
    center,
    corners,
    bounds: {
      minX: Math.min(...xs),
      maxX: Math.max(...xs),
      minY: Math.min(...ys),
      maxY: Math.max(...ys),
    },
  };
}

/**
 * Собирает спроецированную геометрию footprint authored-объекта.
 */
function projectObjectFootprint(object: SceneObject & { x: number; y: number }): ProjectedObjectFootprint {
  const x0 = resolveWorldCoordinate(object.x, gridWidth.value);
  const y0 = resolveWorldCoordinate(object.y, gridHeight.value);
  const x1 = x0 + (TILE_WORLD_SIZE * Math.max(1, object.width));
  const y1 = y0 + (TILE_WORLD_SIZE * Math.max(1, object.height));
  const anchorCell = getCell(object.x, object.y);
  const z = ((anchorCell?.elevation ?? 0) * ELEVATION_STEP);
  const corners = [
    projectWorldPoint(x0, y0, z),
    projectWorldPoint(x1, y0, z),
    projectWorldPoint(x1, y1, z),
    projectWorldPoint(x0, y1, z),
  ];
  const center = projectWorldPoint((x0 + x1) / 2, (y0 + y1) / 2, z);
  const xs = corners.map((corner) => corner.x);
  const ys = corners.map((corner) => corner.y);

  return {
    object,
    center,
    corners,
    bounds: {
      minX: Math.min(...xs),
      maxX: Math.max(...xs),
      minY: Math.min(...ys),
      maxY: Math.max(...ys),
    },
  };
}

/**
 * Возвращает отсортированный список спроецированных authored-клеток.
 */
function getProjectedCells(): ProjectedCell[] {
  return sceneCells.value
    .map((cell) => projectCell(cell))
    .sort((left, right) => left.center.y - right.center.y);
}

/**
 * Возвращает размер карточки актора для конкретной клетки.
 */
function resolveActorCardSize(cellProjection: ProjectedCell): {
  height: number;
  width: number;
} {
  void cellProjection;
  const width = Math.max(44, Math.round((TILE_WORLD_SIZE * viewport.value.zoom * 0.62) - 10));
  const height = Math.round(width * (4 / 3));

  return {
    width,
    height,
  };
}

/**
 * Возвращает ключ клетки по координатам.
 */
function resolveCellKey(x: number, y: number): string {
  return `${x}-${y}`;
}

/**
 * Возвращает true, если точка находится внутри полигона клетки.
 */
function isPointInsidePolygon(point: CanvasPoint, polygon: CanvasPoint[]): boolean {
  let isInside = false;

  for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i, i += 1) {
    const pointA = polygon[i];
    const pointB = polygon[j];
    const intersects = ((pointA.y > point.y) !== (pointB.y > point.y))
      && (point.x < (((pointB.x - pointA.x) * (point.y - pointA.y)) / ((pointB.y - pointA.y) || 0.00001)) + pointA.x);

    if (intersects) {
      isInside = !isInside;
    }
  }

  return isInside;
}

/**
 * Возвращает клетку под указателем canvas.
 */
function findCellAtCanvasPoint(point: CanvasPoint): ProjectedCell | null {
  const projectedCells = getProjectedCells();

  for (let index = projectedCells.length - 1; index >= 0; index -= 1) {
    const projectedCell = projectedCells[index];

    if (
      point.x >= projectedCell.bounds.minX
      && point.x <= projectedCell.bounds.maxX
      && point.y >= projectedCell.bounds.minY
      && point.y <= projectedCell.bounds.maxY
      && isPointInsidePolygon(point, projectedCell.corners)
    ) {
      return projectedCell;
    }
  }

  return null;
}

/**
 * Нормализует точку события мыши относительно canvas.
 */
function resolveCanvasPoint(event: MouseEvent): CanvasPoint | null {
  const canvas = canvasRef.value;

  if (canvas === null) {
    return null;
  }

  const rect = canvas.getBoundingClientRect();

  if (
    event.clientX < rect.left
    || event.clientX > rect.right
    || event.clientY < rect.top
    || event.clientY > rect.bottom
  ) {
    return null;
  }

  return {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top,
  };
}

/**
 * Обрабатывает обычный клик по authored-клетке.
 */
function handleCellAction(x: number, y: number): void {
  selectedCellKey.value = resolveCellKey(x, y);
  const hasObject = getObjectAtCell(x, y) !== undefined;
  const hasActor = getActorPlacementAtCell(x, y) !== undefined;

  if (activeEraseMode.value) {
    eraseCellContent(x, y);
    return;
  }

  if (hasObject || hasActor) {
    return;
  }

  paintCell(x, y);
}

/**
 * Запоминает режим drag/rotate в viewport.
 */
function handleCanvasMouseDown(event: MouseEvent): void {
  if (canvasRef.value === null) {
    return;
  }

  if (event.button === 2) {
    return;
  }

  hasMoved.value = false;
  pointerStartX.value = event.clientX;
  pointerStartY.value = event.clientY;
  pointerStartOffsetX.value = viewport.value.offsetX;
  pointerStartOffsetY.value = viewport.value.offsetY;
  pointerStartRotateX.value = viewport.value.rotateX;
  pointerStartRotateZ.value = viewport.value.rotateZ;
  hoveredActorTooltip.value = null;

  if (event.button === 0) {
    const point = resolveCanvasPoint(event);
    const targetCell = point ? findCellAtCanvasPoint(point) : null;
    const hasEntity = targetCell !== null
      && (
        getObjectAtCell(targetCell.cell.x, targetCell.cell.y) !== undefined
        || getActorPlacementAtCell(targetCell.cell.x, targetCell.cell.y) !== undefined
      );

    if (targetCell !== null && (activeEraseMode.value || !hasEntity)) {
      isPaintDragging.value = true;
      handleCellAction(targetCell.cell.x, targetCell.cell.y);
      scheduleCanvasRender();
      event.preventDefault();

      return;
    }

    pointerMode.value = 'pan';
    event.preventDefault();
  }

  if (event.button === 1) {
    pointerMode.value = 'rotate';
    event.preventDefault();
  }
}

/**
 * Открывает контекстное меню редактора по правому клику на клетке.
 */
function handleCanvasContextMenu(event: MouseEvent): void {
  const point = resolveCanvasPoint(event);
  const targetCell = point ? findCellAtCanvasPoint(point) : null;

  if (targetCell === null) {
    closeEditorContextMenu();
    return;
  }

  selectedCellKey.value = resolveCellKey(targetCell.cell.x, targetCell.cell.y);
  hoveredActorTooltip.value = null;
  editorContextMenu.value = {
    cellX: targetCell.cell.x,
    cellY: targetCell.cell.y,
    x: event.clientX,
    y: event.clientY,
  };
  scheduleCanvasRender();
}

/**
 * Обрабатывает движение мыши для canvas viewport.
 */
function handleGlobalMouseMove(event: MouseEvent): void {
  if (isPaintDragging.value) {
    const point = resolveCanvasPoint(event);
    const hoveredProjection = point ? findCellAtCanvasPoint(point) : null;

    if (hoveredProjection !== null) {
      const nextHoveredCellKey = resolveCellKey(hoveredProjection.cell.x, hoveredProjection.cell.y);
      hoveredCellKey.value = nextHoveredCellKey;

      if (selectedCellKey.value !== nextHoveredCellKey) {
        handleCellAction(hoveredProjection.cell.x, hoveredProjection.cell.y);
      }
    }

    scheduleCanvasRender();

    return;
  }

  const deltaX = event.clientX - pointerStartX.value;
  const deltaY = event.clientY - pointerStartY.value;
  const passedThreshold = Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4;

  if (pointerMode.value !== null && passedThreshold) {
    hasMoved.value = true;
  }

  if (pointerMode.value === 'pan' && hasMoved.value) {
    viewport.value = {
      ...viewport.value,
      offsetX: pointerStartOffsetX.value + deltaX,
      offsetY: pointerStartOffsetY.value + deltaY,
    };
    scheduleCanvasRender();

    return;
  }

  if (pointerMode.value === 'rotate' && hasMoved.value) {
    viewport.value = {
      ...viewport.value,
      rotateX: Math.min(78, Math.max(28, pointerStartRotateX.value - (deltaY * 0.25))),
      rotateZ: Math.min(12, Math.max(-88, pointerStartRotateZ.value + (deltaX * 0.35))),
    };
    scheduleCanvasRender();

    return;
  }

  const point = resolveCanvasPoint(event);

  if (point === null) {
    hoveredCellKey.value = null;
    hoveredActorTooltip.value = null;
    scheduleCanvasRender();

    return;
  }

  const hoveredProjection = findCellAtCanvasPoint(point);

  if (hoveredProjection === null) {
    hoveredCellKey.value = null;
    hoveredActorTooltip.value = null;
    scheduleCanvasRender();

    return;
  }

  const nextHoveredCellKey = `${hoveredProjection.cell.x}-${hoveredProjection.cell.y}`;
  const placement = getActorPlacementAtCell(hoveredProjection.cell.x, hoveredProjection.cell.y);
  hoveredCellKey.value = nextHoveredCellKey;
  hoveredActorTooltip.value = placement
    ? {
        name: placement.actor.name,
        x: point.x + 14,
        y: point.y - 10,
      }
    : null;
  scheduleCanvasRender();
}

/**
 * Завершает drag/rotate и обрабатывает обычный клик.
 */
function handleGlobalMouseUp(event: MouseEvent): void {
  if (isPaintDragging.value) {
    isPaintDragging.value = false;
    hasMoved.value = false;

    return;
  }

  const mode = pointerMode.value;
  pointerMode.value = null;

  if (event.button === 2) {
    return;
  }

  if (mode === 'pan' && !hasMoved.value) {
    const point = resolveCanvasPoint(event);
    const targetCell = point ? findCellAtCanvasPoint(point) : null;

    if (targetCell !== null) {
      handleCellAction(targetCell.cell.x, targetCell.cell.y);
      scheduleCanvasRender();
    }
  }

  hasMoved.value = false;
}

/**
 * Запрещает browser autoscroll на среднем клике.
 */
function handleCanvasMouseLeave(): void {
  if (pointerMode.value !== null) {
    return;
  }

  hoveredCellKey.value = null;
  hoveredActorTooltip.value = null;
  scheduleCanvasRender();
}

/**
 * Удаляет выбранную сущность с клавиатуры.
 */
function handleGlobalKeyDown(event: KeyboardEvent): void {
  if (selectedCellKey.value === null) {
    return;
  }

  if (event.key !== 'Delete' && event.key !== 'Backspace') {
    return;
  }

  const [xRaw, yRaw] = selectedCellKey.value.split('-');
  const x = Number.parseInt(xRaw ?? '', 10);
  const y = Number.parseInt(yRaw ?? '', 10);

  if (Number.isNaN(x) || Number.isNaN(y)) {
    return;
  }

  if (getObjectAtCell(x, y) === undefined && getActorPlacementAtCell(x, y) === undefined) {
    return;
  }

  event.preventDefault();
  eraseCellContent(x, y);
  scheduleCanvasRender();
}

/**
 * Меняет zoom viewport колесом мыши.
 */
function handleCanvasWheel(event: WheelEvent): void {
  event.preventDefault();

  const nextZoom = Math.min(1.8, Math.max(0.55, viewport.value.zoom + (event.deltaY < 0 ? 0.08 : -0.08)));

  if (Math.abs(nextZoom - viewport.value.zoom) < 0.001) {
    return;
  }

  viewport.value = {
    ...viewport.value,
    zoom: Number.parseFloat(nextZoom.toFixed(2)),
  };
  scheduleCanvasRender();
}

/**
 * Настраивает реальные размеры canvas под контейнер.
 */
function resizeCanvas(): void {
  const canvas = canvasRef.value;
  const viewportElement = canvasViewportRef.value;

  if (canvas === null || viewportElement === null) {
    return;
  }

  const bounds = viewportElement.getBoundingClientRect();
  const width = Math.max(320, Math.round(bounds.width));
  const height = Math.max(MIN_CANVAS_HEIGHT, Math.round(bounds.height));
  const dpr = window.devicePixelRatio || 1;

  canvas.width = Math.round(width * dpr);
  canvas.height = Math.round(height * dpr);
  canvas.style.width = `${width}px`;
  canvas.style.height = `${height}px`;
  canvasSize.value = { width, height };
  scheduleCanvasRender();
}

/**
 * Помещает путь полигона клетки в текущий canvas context.
 */
function tracePolygon(context: CanvasRenderingContext2D, corners: CanvasPoint[]): void {
  context.beginPath();
  context.moveTo(corners[0].x, corners[0].y);

  for (let index = 1; index < corners.length; index += 1) {
    context.lineTo(corners[index].x, corners[index].y);
  }

  context.closePath();
}

/**
 * Отрисовывает одну клетку сцены.
 */
function drawCell(context: CanvasRenderingContext2D, projectedCell: ProjectedCell): void {
  const palette = resolveSurfacePalette(projectedCell.cell.terrain_type);
  const textureImage = resolveSurfaceTexture(projectedCell.cell.terrain_type as SceneSurfaceDefinition['code']);
  const gradient = context.createLinearGradient(
    projectedCell.bounds.minX,
    projectedCell.bounds.minY,
    projectedCell.bounds.maxX,
    projectedCell.bounds.maxY,
  );
  const cellKey = resolveCellKey(projectedCell.cell.x, projectedCell.cell.y);
  const isSelected = cellKey === selectedCellKey.value;
  const isHovered = cellKey === hoveredCellKey.value;
  gradient.addColorStop(0, palette.accent);
  gradient.addColorStop(0.24, palette.fill);
  gradient.addColorStop(1, palette.shadow);

  context.save();
  if ((projectedCell.cell.elevation ?? 0) > 0) {
    const dropDepth = Math.max(8, (projectedCell.cell.elevation ?? 0) * 6);
    const bottomCorners = projectedCell.corners.map((corner) => ({
      x: corner.x,
      y: corner.y + dropDepth,
    }));

    context.beginPath();
    context.moveTo(projectedCell.corners[2].x, projectedCell.corners[2].y);
    context.lineTo(bottomCorners[2].x, bottomCorners[2].y);
    context.lineTo(bottomCorners[3].x, bottomCorners[3].y);
    context.lineTo(projectedCell.corners[3].x, projectedCell.corners[3].y);
    context.closePath();
    context.fillStyle = 'rgba(15, 23, 42, 0.26)';
    context.fill();

    context.beginPath();
    context.moveTo(projectedCell.corners[1].x, projectedCell.corners[1].y);
    context.lineTo(bottomCorners[1].x, bottomCorners[1].y);
    context.lineTo(bottomCorners[2].x, bottomCorners[2].y);
    context.lineTo(projectedCell.corners[2].x, projectedCell.corners[2].y);
    context.closePath();
    context.fillStyle = 'rgba(15, 23, 42, 0.18)';
    context.fill();
  }

  tracePolygon(context, projectedCell.corners);
  context.fillStyle = gradient;
  context.fill();

  if (textureImage !== null) {
    context.save();
    tracePolygon(context, projectedCell.corners);
    context.clip();
    context.globalAlpha = 0.42;
    const topLeft = projectedCell.corners[0];
    const topRight = projectedCell.corners[1];
    const bottomLeft = projectedCell.corners[3];
    const basisX = {
      x: topRight.x - topLeft.x,
      y: topRight.y - topLeft.y,
    };
    const basisY = {
      x: bottomLeft.x - topLeft.x,
      y: bottomLeft.y - topLeft.y,
    };
    context.transform(
      basisX.x / textureImage.width,
      basisX.y / textureImage.width,
      basisY.x / textureImage.height,
      basisY.y / textureImage.height,
      topLeft.x,
      topLeft.y,
    );
    context.drawImage(textureImage, 0, 0);
    context.restore();
  }

  context.lineWidth = isSelected ? 3.2 : isHovered ? 2.4 : 1.2;
  context.strokeStyle = isSelected ? '#facc15' : isHovered ? '#fff6d5' : palette.stroke;
  context.stroke();

  const sheen = context.createLinearGradient(
    projectedCell.bounds.minX,
    projectedCell.bounds.minY,
    projectedCell.bounds.maxX,
    projectedCell.bounds.minY + ((projectedCell.bounds.maxY - projectedCell.bounds.minY) * 0.7),
  );
  sheen.addColorStop(0, 'rgba(255,255,255,0.22)');
  sheen.addColorStop(0.36, 'rgba(255,255,255,0.05)');
  sheen.addColorStop(1, 'rgba(255,255,255,0)');
  tracePolygon(context, projectedCell.corners);
  context.fillStyle = sheen;
  context.fill();

  context.globalAlpha = 0.18;
  context.beginPath();
  context.moveTo(projectedCell.corners[0].x, projectedCell.corners[0].y);
  context.lineTo(projectedCell.corners[2].x, projectedCell.corners[2].y);
  context.moveTo(projectedCell.corners[1].x, projectedCell.corners[1].y);
  context.lineTo(projectedCell.corners[3].x, projectedCell.corners[3].y);
  context.strokeStyle = '#fff';
  context.stroke();

  if (isSelected) {
    context.globalAlpha = 0.14;
    tracePolygon(context, projectedCell.corners);
    context.fillStyle = '#facc15';
    context.fill();
  }
  context.restore();
}

/**
 * Отрисовывает куст на конкретной клетке.
 */
function drawBush(context: CanvasRenderingContext2D, footprint: ProjectedObjectFootprint, isSelected = false): void {
  const baseX = footprint.center.x;
  const objectWidthInCells = Math.max(1, footprint.object.width);
  const objectHeightInCells = Math.max(1, footprint.object.height);
  const isMultiCellObject = objectWidthInCells > 1 || objectHeightInCells > 1;
  const billboardWidth = Math.max(44, objectWidthInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.72);
  const billboardHeight = Math.max(44, objectHeightInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.72);
  const scale = Math.max(0.72, viewport.value.zoom);
  const baseY = isMultiCellObject ? footprint.center.y : footprint.center.y - 8;

  context.save();
  context.translate(baseX, baseY);

  context.fillStyle = 'rgba(15, 23, 42, 0.34)';
  context.beginPath();
  context.ellipse(0, 6, 28 * scale, 14 * scale, 0, 0, Math.PI * 2);
  context.fill();

  context.fillStyle = '#244229';
  context.beginPath();
  context.ellipse(0, 2, 22 * scale, 10 * scale, 0, 0, Math.PI * 2);
  context.fill();

  const foliage = [
    { color: '#4ade80', radius: 26, x: 0, y: -42 },
    { color: '#3abf68', radius: 22, x: -22, y: -28 },
    { color: '#34a853', radius: 22, x: 20, y: -26 },
    { color: '#72e09b', radius: 20, x: 0, y: -16 },
    { color: '#2f8f49', radius: 16, x: -8, y: -8 },
    { color: '#66cc86', radius: 16, x: 10, y: -10 },
  ];

  for (const leaf of foliage) {
    const gradient = context.createRadialGradient(
      leaf.x - (leaf.radius * 0.3),
      leaf.y - (leaf.radius * 0.4),
      2,
      leaf.x,
      leaf.y,
      leaf.radius * scale,
    );
    gradient.addColorStop(0, '#ecfccb');
    gradient.addColorStop(0.35, leaf.color);
    gradient.addColorStop(1, '#166534');
    context.fillStyle = gradient;
    context.beginPath();
    context.arc((leaf.x * billboardWidth) / 180, (leaf.y * billboardHeight) / 180, Math.max(10, (leaf.radius * scale * (objectWidthInCells + objectHeightInCells)) / 2.4), 0, Math.PI * 2);
    context.fill();

    context.lineWidth = 1.2;
    context.strokeStyle = 'rgba(236, 252, 203, 0.28)';
    context.stroke();
  }

  if (isSelected) {
    context.strokeStyle = 'rgba(250, 204, 21, 0.9)';
    context.lineWidth = 3;
    context.beginPath();
    context.ellipse(0, -(billboardHeight * 0.18), billboardWidth * 0.24, billboardHeight * 0.2, 0, 0, Math.PI * 2);
    context.stroke();
  }

  context.restore();
}

/**
 * Отрисовывает бочку на конкретной клетке.
 */
function drawBarrel(context: CanvasRenderingContext2D, footprint: ProjectedObjectFootprint, isSelected = false): void {
  const baseX = footprint.center.x;
  const objectWidthInCells = Math.max(1, footprint.object.width);
  const objectHeightInCells = Math.max(1, footprint.object.height);
  const isMultiCellObject = objectWidthInCells > 1 || objectHeightInCells > 1;
  const scale = Math.max(0.76, viewport.value.zoom);
  const bodyWidth = Math.max(34, objectWidthInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.3);
  const bodyHeight = Math.max(50, objectHeightInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.42);
  const baseY = isMultiCellObject ? footprint.center.y : footprint.center.y - 6;

  context.save();
  context.translate(baseX, baseY);

  context.fillStyle = 'rgba(15, 23, 42, 0.28)';
  context.beginPath();
  context.ellipse(0, 6, 22 * scale, 12 * scale, 0, 0, Math.PI * 2);
  context.fill();

  const bodyGradient = context.createLinearGradient(-(bodyWidth / 2), 0, bodyWidth / 2, 0);
  bodyGradient.addColorStop(0, '#6f3f1e');
  bodyGradient.addColorStop(0.2, '#b56f35');
  bodyGradient.addColorStop(0.5, '#d28d49');
  bodyGradient.addColorStop(0.8, '#a85f2f');
  bodyGradient.addColorStop(1, '#5b3418');
  context.fillStyle = bodyGradient;
  context.fillRect(-(bodyWidth / 2), -bodyHeight, bodyWidth, bodyHeight);

  for (let index = -1; index <= 1; index += 1) {
    context.fillStyle = index === 0 ? 'rgba(244, 193, 133, 0.22)' : 'rgba(98, 56, 26, 0.18)';
    context.fillRect((index * 10 * scale) - (5 * scale), -bodyHeight, 10 * scale, bodyHeight);
  }

  context.strokeStyle = '#3a2717';
  context.lineWidth = 3.2;
  context.beginPath();
  context.ellipse(0, -bodyHeight, bodyWidth / 2, 10 * scale, 0, 0, Math.PI * 2);
  context.stroke();

  context.beginPath();
  context.ellipse(0, -bodyHeight + (bodyHeight * 0.3), bodyWidth / 2, 8 * scale, 0, 0, Math.PI * 2);
  context.stroke();

  context.beginPath();
  context.ellipse(0, -bodyHeight + (bodyHeight * 0.68), bodyWidth / 2, 8 * scale, 0, 0, Math.PI * 2);
  context.stroke();

  context.strokeStyle = '#c8b89f';
  context.lineWidth = 2;
  context.beginPath();
  context.ellipse(0, -bodyHeight + (bodyHeight * 0.3), bodyWidth / 2, 8 * scale, 0, 0, Math.PI * 2);
  context.stroke();

  context.beginPath();
  context.ellipse(0, -bodyHeight + (bodyHeight * 0.68), bodyWidth / 2, 8 * scale, 0, 0, Math.PI * 2);
  context.stroke();

  context.fillStyle = '#c48a53';
  context.beginPath();
  context.ellipse(0, -bodyHeight, bodyWidth / 2, 10 * scale, 0, 0, Math.PI * 2);
  context.fill();
  context.stroke();

  context.fillStyle = 'rgba(255, 241, 212, 0.22)';
  context.beginPath();
  context.ellipse(-(bodyWidth * 0.14), -bodyHeight - (2 * scale), bodyWidth * 0.18, 4 * scale, -0.18, 0, Math.PI * 2);
  context.fill();

  if (isSelected) {
    context.strokeStyle = 'rgba(250, 204, 21, 0.95)';
    context.lineWidth = 3;
    context.beginPath();
    context.ellipse(0, -bodyHeight * 0.48, (bodyWidth / 2) + 6, (bodyHeight / 2) + 4, 0, 0, Math.PI * 2);
    context.stroke();
  }
  context.restore();
}

/**
 * Отрисовывает authored-объект картинкой с fallback на старый procedural-рендер.
 */
function drawSceneObject(
  context: CanvasRenderingContext2D,
  footprint: ProjectedObjectFootprint,
  objectKind: SceneObjectDefinition['code'],
  isSelected = false,
): void {
  const image = resolveSceneObjectImage(objectKind);

  if (image === null) {
    if (objectKind === 'bush') {
      drawBush(context, footprint, isSelected);
    }

    if (objectKind === 'barrel') {
      drawBarrel(context, footprint, isSelected);
    }

    if (objectKind === 'house') {
      const footprintWidth = footprint.bounds.maxX - footprint.bounds.minX;
      const footprintHeight = footprint.bounds.maxY - footprint.bounds.minY;
      const objectWidthInCells = Math.max(1, footprint.object.width);
      const objectHeightInCells = Math.max(1, footprint.object.height);
      const baseX = footprint.center.x;
      const baseY = footprint.center.y;
      const bodyWidth = Math.max(140, objectWidthInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.86);
      const bodyHeight = Math.max(96, objectHeightInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.52);
      const roofHeight = Math.max(64, bodyHeight * 0.7);
      const doorWidth = bodyWidth * 0.18;
      const doorHeight = bodyHeight * 0.42;
      const windowWidth = bodyWidth * 0.16;
      const windowHeight = bodyHeight * 0.22;
      context.save();
      context.translate(baseX, baseY);
      context.fillStyle = 'rgba(15, 23, 42, 0.34)';
      context.beginPath();
      context.ellipse(0, 10, Math.max(24, footprintWidth * 0.32), Math.max(18, footprintHeight * 0.18), 0, 0, Math.PI * 2);
      context.fill();
      context.fillStyle = '#7c4a2d';
      context.fillRect(-(bodyWidth / 2), -bodyHeight, bodyWidth, bodyHeight);
      context.fillStyle = '#c2410c';
      context.beginPath();
      context.moveTo(-(bodyWidth * 0.62), -(bodyHeight * 0.96));
      context.lineTo(0, -(bodyHeight + roofHeight));
      context.lineTo(bodyWidth * 0.62, -(bodyHeight * 0.96));
      context.closePath();
      context.fill();
      context.fillStyle = '#f5d0a9';
      context.fillRect(-(doorWidth / 2), -doorHeight, doorWidth, doorHeight);
      context.fillRect(-(bodyWidth * 0.32), -(bodyHeight * 0.68), windowWidth, windowHeight);
      context.fillRect(bodyWidth * 0.16, -(bodyHeight * 0.68), windowWidth, windowHeight);
      if (isSelected) {
        context.strokeStyle = 'rgba(250, 204, 21, 0.95)';
        context.lineWidth = 4;
        context.strokeRect(-(bodyWidth * 0.56), -(bodyHeight + roofHeight + 8), bodyWidth * 1.12, bodyHeight + roofHeight + 24);
      }
      context.restore();
    }

    return;
  }

  const footprintHeight = footprint.bounds.maxY - footprint.bounds.minY;
  const objectWidthInCells = Math.max(1, footprint.object.width);
  const objectHeightInCells = Math.max(1, footprint.object.height);
  const isMultiCellObject = objectWidthInCells > 1 || objectHeightInCells > 1;
  const baseX = footprint.center.x;
  const baseY = isMultiCellObject
    ? footprint.center.y
    : footprint.bounds.maxY - Math.max(8, footprintHeight * 0.16);
  const maxWidth = Math.max(46, objectWidthInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.82);
  const maxHeight = Math.max(60, objectHeightInCells * TILE_WORLD_SIZE * viewport.value.zoom * 1.08);
  const sourceRatio = image.width / image.height;
  let drawWidth = maxWidth;
  let drawHeight = drawWidth / sourceRatio;

  if (drawHeight > maxHeight) {
    drawHeight = maxHeight;
    drawWidth = drawHeight * sourceRatio;
  }

  const drawX = baseX - (drawWidth / 2);
  const drawY = isMultiCellObject ? baseY - (drawHeight / 2) : baseY - drawHeight;

  context.save();
  context.fillStyle = 'rgba(15, 23, 42, 0.34)';
  context.beginPath();
  context.ellipse(baseX, baseY + 6, Math.max(18, (footprint.bounds.maxX - footprint.bounds.minX) * 0.3), Math.max(10, footprintHeight * 0.16), 0, 0, Math.PI * 2);
  context.fill();
  context.drawImage(image, drawX, drawY, drawWidth, drawHeight);

  if (isSelected) {
    context.strokeStyle = 'rgba(250, 204, 21, 0.95)';
    context.lineWidth = 3;
    context.strokeRect(drawX - 2, drawY - 2, drawWidth + 4, drawHeight + 4);
  }

  context.restore();
}

/**
 * Возвращает загруженное изображение актора, если оно уже доступно.
 */
function resolveActorImage(actor: GameActor): HTMLImageElement | null {
  return resolveCachedImage(actor.image_url);
}

/**
 * Отрисовывает карточку актора на конкретной клетке.
 */
function drawActor(context: CanvasRenderingContext2D, projectedCell: ProjectedCell, placement: SceneActorPlacement, isSelected = false): void {
  const { actor } = placement;
  const isPlayerHero = actor.kind === 'player_character';
  const baseX = projectedCell.center.x;
  const baseY = projectedCell.center.y - 8;
  const cardSize = resolveActorCardSize(projectedCell);
  const cardHeight = cardSize.height;
  const cardTop = baseY - cardHeight;
  const baseWidth = Math.max(42, cardSize.width - 2);
  const imageHeight = cardHeight;

  context.save();
  context.translate(baseX, baseY);

  context.fillStyle = 'rgba(15, 23, 42, 0.34)';
  context.beginPath();
  context.ellipse(0, 6, baseWidth * 0.42, 10, 0, 0, Math.PI * 2);
  context.fill();

  const baseGradient = context.createLinearGradient(0, -10, 0, 10);
  baseGradient.addColorStop(0, isPlayerHero ? '#dbeafe' : '#efdbba');
  baseGradient.addColorStop(0.35, isPlayerHero ? '#60a5fa' : '#c69a62');
  baseGradient.addColorStop(1, isPlayerHero ? '#1e3a8a' : '#5d381d');
  context.fillStyle = baseGradient;
  context.beginPath();
  context.ellipse(0, 0, baseWidth * 0.45, 8.5, 0, 0, Math.PI * 2);
  context.fill();

  context.strokeStyle = isPlayerHero ? 'rgba(219, 234, 254, 0.7)' : 'rgba(255, 240, 210, 0.45)';
  context.lineWidth = 1.4;
  context.stroke();

  context.fillStyle = isPlayerHero ? '#60a5fa' : '#b08a57';
  context.fillRect(-2.2, -22, 4.4, 24);
  context.fillStyle = isPlayerHero ? '#e0f2fe' : '#f3d8a6';
  context.fillRect(-0.9, -22, 1.8, 24);

  context.restore();

  context.save();
  const cardX = baseX - (cardSize.width / 2);
  const cardY = cardTop;
  const radius = 12;
  const frameFill = context.createLinearGradient(cardX, cardY, cardX + cardSize.width, cardY + cardHeight);
  frameFill.addColorStop(0, isPlayerHero ? '#eff6ff' : '#f5e7c8');
  frameFill.addColorStop(0.3, isPlayerHero ? '#93c5fd' : '#c6975b');
  frameFill.addColorStop(0.7, isPlayerHero ? '#1d4ed8' : '#7e5734');
  frameFill.addColorStop(1, isPlayerHero ? '#dbeafe' : '#f3ddb2');

  context.fillStyle = frameFill;
  context.beginPath();
  context.moveTo(cardX + radius, cardY);
  context.lineTo(cardX + cardSize.width - radius, cardY);
  context.quadraticCurveTo(cardX + cardSize.width, cardY, cardX + cardSize.width, cardY + radius);
  context.lineTo(cardX + cardSize.width, cardY + cardHeight - radius);
  context.quadraticCurveTo(cardX + cardSize.width, cardY + cardHeight, cardX + cardSize.width - radius, cardY + cardHeight);
  context.lineTo(cardX + radius, cardY + cardHeight);
  context.quadraticCurveTo(cardX, cardY + cardHeight, cardX, cardY + cardHeight - radius);
  context.lineTo(cardX, cardY + radius);
  context.quadraticCurveTo(cardX, cardY, cardX + radius, cardY);
  context.closePath();
  context.fill();

  const inset = 4;

  context.beginPath();
  context.moveTo(cardX + inset + radius, cardY + inset);
  context.lineTo(cardX + cardSize.width - inset - radius, cardY + inset);
  context.quadraticCurveTo(cardX + cardSize.width - inset, cardY + inset, cardX + cardSize.width - inset, cardY + inset + radius);
  context.lineTo(cardX + cardSize.width - inset, cardY + cardHeight - inset - radius);
  context.quadraticCurveTo(cardX + cardSize.width - inset, cardY + cardHeight - inset, cardX + cardSize.width - inset - radius, cardY + cardHeight - inset);
  context.lineTo(cardX + inset + radius, cardY + cardHeight - inset);
  context.quadraticCurveTo(cardX + inset, cardY + cardHeight - inset, cardX + inset, cardY + cardHeight - inset - radius);
  context.lineTo(cardX + inset, cardY + inset + radius);
  context.quadraticCurveTo(cardX + inset, cardY + inset, cardX + inset + radius, cardY + inset);
  context.closePath();
  context.clip();

  const image = resolveActorImage(actor);

  if (image !== null) {
    const sourceRatio = image.width / image.height;
    const targetRatio = cardSize.width / imageHeight;
    let sourceWidth = image.width;
    let sourceHeight = image.height;
    let sourceX = 0;
    let sourceY = 0;

    if (sourceRatio > targetRatio) {
      sourceWidth = image.height * targetRatio;
      sourceX = (image.width - sourceWidth) / 2;
    } else {
      sourceHeight = image.width / targetRatio;
      sourceY = 0;
    }

    context.drawImage(
      image,
      sourceX,
      sourceY,
      sourceWidth,
      sourceHeight,
      cardX + inset,
      cardY + inset,
      cardSize.width - (inset * 2),
      imageHeight - (inset * 2),
    );
  } else {
    const placeholderGradient = context.createLinearGradient(cardX, cardY, cardX, cardY + cardHeight);
    placeholderGradient.addColorStop(0, '#5b446f');
    placeholderGradient.addColorStop(1, '#160f23');
    context.fillStyle = placeholderGradient;
    context.fillRect(cardX, cardY, cardSize.width, cardHeight);
    context.fillStyle = '#fef3c7';
    context.font = '700 28px Vollkorn, serif';
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.fillText(actor.name.slice(0, 1).toUpperCase(), baseX, cardY + (cardHeight / 2));
  }

  const gloss = context.createLinearGradient(cardX, cardY, cardX, cardY + (cardHeight * 0.45));
  gloss.addColorStop(0, 'rgba(255,255,255,0.28)');
  gloss.addColorStop(0.35, 'rgba(255,255,255,0.08)');
  gloss.addColorStop(1, 'rgba(255,255,255,0)');
  context.fillStyle = gloss;
  context.fillRect(cardX + inset, cardY + inset, cardSize.width - (inset * 2), (cardHeight * 0.42));

  context.restore();

  context.save();
  context.lineWidth = isSelected ? 3 : isPlayerHero ? 2.8 : actor.kind === 'npc' ? 2.7 : 2.1;
  context.strokeStyle = isSelected ? '#facc15' : isPlayerHero ? '#bfdbfe' : actor.kind === 'npc' ? '#f6d48b' : '#f8fafc';
  context.shadowBlur = isPlayerHero ? 26 : 18;
  context.shadowColor = isPlayerHero ? 'rgba(96, 165, 250, 0.42)' : 'rgba(15, 23, 42, 0.34)';
  context.beginPath();
  context.moveTo(cardX + radius, cardY);
  context.lineTo(cardX + cardSize.width - radius, cardY);
  context.quadraticCurveTo(cardX + cardSize.width, cardY, cardX + cardSize.width, cardY + radius);
  context.lineTo(cardX + cardSize.width, cardY + cardHeight - radius);
  context.quadraticCurveTo(cardX + cardSize.width, cardY + cardHeight, cardX + cardSize.width - radius, cardY + cardHeight);
  context.lineTo(cardX + radius, cardY + cardHeight);
  context.quadraticCurveTo(cardX, cardY + cardHeight, cardX, cardY + cardHeight - radius);
  context.lineTo(cardX, cardY + radius);
  context.quadraticCurveTo(cardX, cardY, cardX + radius, cardY);
  context.closePath();
  context.stroke();

  context.lineWidth = 1;
  context.strokeStyle = isSelected ? 'rgba(254, 240, 138, 0.85)' : isPlayerHero ? 'rgba(224, 242, 254, 0.85)' : 'rgba(255, 247, 228, 0.6)';
  context.stroke();
  context.restore();
}

/**
 * Отрисовывает ghost-preview инструмента на наведенной клетке.
 */
/**
 * Отрисовывает все объекты и актеров поверх клеток.
 */
function drawSceneEntities(context: CanvasRenderingContext2D, projectedCells: ProjectedCell[]): void {
  const objectPlacements = sceneObjects.value
    .filter((object): object is SceneObject & { x: number; y: number } => object.x !== null && object.y !== null)
    .map((object) => ({
      object,
      footprint: projectObjectFootprint(object),
    }))
    .sort((left, right) => left.footprint.bounds.maxY - right.footprint.bounds.maxY);

  objectPlacements.forEach(({ object, footprint }) => {
    const isSelected = selectedCell.value !== null && objectOccupiesCell(object, selectedCell.value.x, selectedCell.value.y);
    drawSceneObject(context, footprint, object.kind, isSelected);
  });

  projectedCells.forEach((projectedCell) => {
    const actorPlacement = getActorPlacementAtCell(projectedCell.cell.x, projectedCell.cell.y);

    if (actorPlacement) {
      const isSelected = selectedCellKey.value === resolveCellKey(projectedCell.cell.x, projectedCell.cell.y);
      drawActor(context, projectedCell, actorPlacement, isSelected);
    }
  });
}

/**
 * Возвращает опорную клетку выбранного authored-объекта.
 */
function resolveObjectAnchorCell(object: SceneObject): SceneCell | null {
  if (object.x === null || object.y === null) {
    return null;
  }

  return getCell(object.x, object.y) ?? null;
}

/**
 * Отрисовывает authored-точку спауна игроков.
 */
function drawPlayerSpawnMarker(context: CanvasRenderingContext2D, projectedCell: ProjectedCell): void {
  const centerX = projectedCell.center.x;
  const centerY = projectedCell.center.y - 24;

  context.save();
  context.fillStyle = 'rgba(16, 185, 129, 0.18)';
  context.beginPath();
  context.arc(centerX, centerY, 18, 0, Math.PI * 2);
  context.fill();

  context.strokeStyle = '#34d399';
  context.lineWidth = 3;
  context.beginPath();
  context.arc(centerX, centerY, 14, 0, Math.PI * 2);
  context.stroke();

  context.strokeStyle = '#ecfdf5';
  context.lineWidth = 2;
  context.beginPath();
  context.moveTo(centerX - 8, centerY);
  context.lineTo(centerX + 8, centerY);
  context.moveTo(centerX, centerY - 8);
  context.lineTo(centerX, centerY + 8);
  context.stroke();
  context.restore();
}

/**
 * Полностью перерисовывает canvas viewport.
 */
function renderCanvasScene(): void {
  const canvas = canvasRef.value;

  if (canvas === null) {
    return;
  }

  const context = canvas.getContext('2d');

  if (context === null) {
    return;
  }

  const dpr = window.devicePixelRatio || 1;
  context.setTransform(dpr, 0, 0, dpr, 0, 0);
  context.clearRect(0, 0, canvasSize.value.width, canvasSize.value.height);
  context.fillStyle = CANVAS_BACKGROUND;
  context.fillRect(0, 0, canvasSize.value.width, canvasSize.value.height);

  const projectedCells = getProjectedCells();

  for (const projectedCell of projectedCells) {
    drawCell(context, projectedCell);
  }

  if (playerSpawnPoint.value !== null) {
    const spawnProjection = projectedCells.find((projectedCell) => projectedCell.cell.x === playerSpawnPoint.value?.x && projectedCell.cell.y === playerSpawnPoint.value?.y);

    if (spawnProjection) {
      drawPlayerSpawnMarker(context, spawnProjection);
    }
  }

  drawSceneEntities(context, projectedCells);

}

/**
 * Планирует безопасную перерисовку canvas через animation frame.
 */
function scheduleCanvasRender(): void {
  if (renderFrameId.value !== null) {
    cancelAnimationFrame(renderFrameId.value);
  }

  renderFrameId.value = window.requestAnimationFrame(() => {
    renderFrameId.value = null;
    renderCanvasScene();
  });
}

/**
 * Подключает ResizeObserver к viewport canvas.
 */
function setupCanvasResizeObserver(): void {
  if (canvasViewportRef.value === null) {
    return;
  }

  resizeObserver?.disconnect();
  resizeObserver = new ResizeObserver(() => {
    resizeCanvas();
  });
  resizeObserver.observe(canvasViewportRef.value);
  resizeCanvas();
}

watch(
  [
    sceneCells,
    sceneObjects,
    sceneActorPlacements,
    viewport,
    gridWidth,
    gridHeight,
    activeTerrain,
    activeEraseMode,
    playerSpawnPoint,
    hoveredCellKey,
    selectedCellKey,
  ],
  () => {
    scheduleCanvasRender();
  },
  { deep: true },
);

watch(
  gameActors,
  () => {
    for (const actor of gameActors.value) {
      resolveActorImage(actor);
    }

    scheduleCanvasRender();
  },
  { deep: true },
);

onMounted(async () => {
  window.addEventListener('mousedown', handleGlobalPointerDown);
  window.addEventListener('mousemove', handleGlobalMouseMove);
  window.addEventListener('mouseup', handleGlobalMouseUp);
  window.addEventListener('keydown', handleGlobalKeyDown);

  await ensureSessionLoaded();

  if (!isAuthenticated.value) {
    await router.replace('/');
    return;
  }

  if (!currentUser.value?.canAccessGm) {
    await router.replace('/cabinet/player');
    return;
  }

  await loadScene();
  await nextTick();
  setupCanvasResizeObserver();
});

onBeforeUnmount(() => {
  window.removeEventListener('mousedown', handleGlobalPointerDown);
  window.removeEventListener('mousemove', handleGlobalMouseMove);
  window.removeEventListener('mouseup', handleGlobalMouseUp);
  window.removeEventListener('keydown', handleGlobalKeyDown);
  resizeObserver?.disconnect();

  if (renderFrameId.value !== null) {
    cancelAnimationFrame(renderFrameId.value);
  }
});
</script>

<template>
  <main
    v-if="currentUser"
    class="fixed inset-0 overflow-hidden bg-[radial-gradient(circle_at_top,rgba(255,221,168,0.18),transparent_22%),linear-gradient(180deg,#120c19_0%,#1f1630_55%,#0b1220_100%)] text-slate-100"
  >
    <div class="pointer-events-none absolute inset-0 opacity-30 [background-image:radial-gradient(circle_at_1px_1px,rgba(255,229,184,0.14)_1px,transparent_0)] [background-size:30px_30px]" />

    <div class="fixed left-6 top-6 z-30 flex flex-wrap items-center gap-3">
      <RouterLink
        class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-slate-950/70 px-4 py-2 text-sm text-amber-50 backdrop-blur transition hover:border-amber-200/40 hover:bg-slate-950/85"
        :to="sceneBackLink"
      >
        <ArrowLeft class="h-4 w-4" />
        Назад
      </RouterLink>

      <button
        class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-2 text-sm text-amber-50 backdrop-blur transition hover:border-amber-200/40 hover:bg-amber-300/15"
        :disabled="isSceneSaving || isSceneLoading || isPending"
        type="button"
        @click="handleSaveScene"
      >
        <Save class="h-4 w-4" />
        Сохранить
      </button>

    </div>

    <div
      v-if="sceneError"
      class="fixed left-6 right-6 top-24 z-20 rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100 backdrop-blur"
    >
      {{ sceneError }}
    </div>

    <div
      v-else-if="isSceneLoading"
      class="fixed left-6 right-6 top-24 z-20 rounded-[1.75rem] border border-amber-200/10 bg-white/5 px-5 py-8 text-sm text-slate-300 backdrop-blur"
    >
      Загружаем сцену...
    </div>

    <template v-else>
      <div
        ref="canvasViewportRef"
        class="scene-editor-viewport"
        @contextmenu.prevent="handleCanvasContextMenu"
        @mousedown="handleCanvasMouseDown"
        @mouseleave="handleCanvasMouseLeave"
        @wheel="handleCanvasWheel"
      >
        <canvas ref="canvasRef" class="scene-editor-canvas" />

        <div
          v-if="hoveredActorTooltip"
          class="scene-hover-tooltip"
          :style="{ left: `${hoveredActorTooltip.x}px`, top: `${hoveredActorTooltip.y}px` }"
        >
          {{ hoveredActorTooltip.name }}
        </div>
      </div>

      <div class="scene-editor-size-widget">
        <div class="scene-editor-size-grid">
          <div />
          <div class="scene-editor-size-side">
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('top', 'shrink')">
              <Minus class="h-3.5 w-3.5" />
            </button>
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('top', 'expand')">
              <Plus class="h-3.5 w-3.5" />
            </button>
          </div>
          <div />
          <div class="scene-editor-size-side scene-editor-size-side-vertical">
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('left', 'shrink')">
              <Minus class="h-3.5 w-3.5" />
            </button>
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('left', 'expand')">
              <Plus class="h-3.5 w-3.5" />
            </button>
          </div>
          <div class="scene-editor-size-center">{{ gridWidth }}x{{ gridHeight }}</div>
          <div class="scene-editor-size-side scene-editor-size-side-vertical">
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('right', 'shrink')">
              <Minus class="h-3.5 w-3.5" />
            </button>
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('right', 'expand')">
              <Plus class="h-3.5 w-3.5" />
            </button>
          </div>
          <div />
          <div class="scene-editor-size-side">
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('bottom', 'shrink')">
              <Minus class="h-3.5 w-3.5" />
            </button>
            <button class="scene-editor-size-button" type="button" @click="resizeGridFromEdge('bottom', 'expand')">
              <Plus class="h-3.5 w-3.5" />
            </button>
          </div>
          <div />
        </div>
      </div>

      <div
        v-if="editorContextMenu"
        class="fixed z-40 min-w-56 rounded-[1.15rem] border border-amber-200/10 bg-slate-950/95 p-2 shadow-[0_18px_50px_rgba(2,6,23,0.55)]"
        :style="{ left: `${editorContextMenu.x}px`, top: `${editorContextMenu.y}px` }"
        @click.stop
        @mousedown.stop
        @contextmenu.prevent.stop
      >
        <div class="mb-2 rounded-xl border border-amber-200/10 bg-white/5 px-3 py-2 text-xs leading-5 text-slate-300">
          Клетка {{ editorContextMenu.cellX }},{{ editorContextMenu.cellY }}: {{ describeCellState(editorContextMenu.cellX, editorContextMenu.cellY) }}
        </div>
        <button
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-amber-50 transition hover:bg-white/5"
          type="button"
          @click.stop="openSurfacePickerForCell(editorContextMenu.cellX, editorContextMenu.cellY)"
        >
          Поверхность
        </button>
        <button
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-amber-50 transition hover:bg-white/5"
          type="button"
          @click.stop="openObjectPickerForCell(editorContextMenu.cellX, editorContextMenu.cellY)"
        >
          Объект
        </button>
        <button
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-amber-50 transition hover:bg-white/5"
          type="button"
          @click.stop="openActorPickerForCell(editorContextMenu.cellX, editorContextMenu.cellY)"
        >
          NPC
        </button>
        <button
          v-if="getObjectAtCell(editorContextMenu.cellX, editorContextMenu.cellY)?.is_interactive"
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-amber-50 transition hover:bg-white/5"
          type="button"
          @click.stop="openObjectInventory(getObjectAtCell(editorContextMenu.cellX, editorContextMenu.cellY)!)"
        >
          Инвентарь объекта
        </button>
        <button
          v-if="playerSpawnPoint?.x === editorContextMenu.cellX && playerSpawnPoint?.y === editorContextMenu.cellY"
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-emerald-100 transition hover:bg-white/5"
          type="button"
          @click.stop="setPlayerSpawnAtCell(editorContextMenu.cellX, editorContextMenu.cellY); closeEditorContextMenu()"
        >
          Убрать спаун игроков
        </button>
        <button
          v-else
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-emerald-100 transition hover:bg-white/5"
          type="button"
          @click.stop="setPlayerSpawnAtCell(editorContextMenu.cellX, editorContextMenu.cellY); closeEditorContextMenu()"
        >
          Спаун игроков
        </button>
        <button
          v-if="getObjectAtCell(editorContextMenu.cellX, editorContextMenu.cellY) || getActorPlacementAtCell(editorContextMenu.cellX, editorContextMenu.cellY)"
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-rose-100 transition hover:bg-white/5"
          type="button"
          @click.stop="eraseCellContent(editorContextMenu.cellX, editorContextMenu.cellY); closeEditorContextMenu()"
        >
          Удалить содержимое
        </button>
        <button
          class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-slate-300 transition hover:bg-white/5"
          type="button"
          @click.stop="closeEditorContextMenu()"
        >
          Закрыть
        </button>
      </div>

      <Teleport to="body">
        <div
          v-if="surfacePickerCell !== null"
          class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/70 p-6 backdrop-blur-sm"
        >
          <div class="absolute inset-0" @click="surfacePickerCell = null" />
          <section class="relative z-10 flex max-h-[min(44rem,calc(100vh-3rem))] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(17,24,39,0.98),rgba(2,6,23,0.98))] shadow-[0_30px_80px_rgba(2,6,23,0.65)]">
            <div class="flex items-start justify-between gap-4 border-b border-amber-200/10 p-6">
              <div>
                <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Поверхность</p>
                <h2 class="mt-2 text-2xl text-amber-50">Клетка {{ surfacePickerCell.x }},{{ surfacePickerCell.y }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ describeCellState(surfacePickerCell.x, surfacePickerCell.y) }}</p>
              </div>
              <button class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-slate-300 transition hover:border-amber-200/30 hover:text-amber-50" type="button" @click="surfacePickerCell = null">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="grid gap-3 overflow-y-auto p-6 sm:grid-cols-2">
              <button
                v-for="surface in surfaceCatalog"
                :key="surface.code"
                class="flex items-center gap-3 rounded-2xl border border-amber-200/10 bg-white/5 p-3 text-left transition hover:border-amber-200/30"
                type="button"
                @click="applySurfaceSelection(surface.code)"
              >
                <span class="terrain-preview">
                  <img v-if="surface.image_url" :src="surface.image_url" :alt="surface.name" class="terrain-preview-image">
                  <span v-else :class="resolveSurfacePreviewClass(surface.code)" class="terrain-preview-fallback" />
                </span>
                <span class="text-sm text-amber-50">{{ surface.name }}</span>
              </button>
            </div>
          </section>
        </div>
      </Teleport>

      <Teleport to="body">
        <div
          v-if="objectPickerCell !== null"
          class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/70 p-6 backdrop-blur-sm"
        >
          <div class="absolute inset-0" @click="objectPickerCell = null" />
          <section class="relative z-10 flex max-h-[min(44rem,calc(100vh-3rem))] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(17,24,39,0.98),rgba(2,6,23,0.98))] shadow-[0_30px_80px_rgba(2,6,23,0.65)]">
            <div class="flex items-start justify-between gap-4 border-b border-amber-200/10 p-6">
              <div>
                <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Объект</p>
                <h2 class="mt-2 text-2xl text-amber-50">Клетка {{ objectPickerCell.x }},{{ objectPickerCell.y }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ describeCellState(objectPickerCell.x, objectPickerCell.y) }}</p>
              </div>
              <button class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-slate-300 transition hover:border-amber-200/30 hover:text-amber-50" type="button" @click="objectPickerCell = null">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="grid gap-3 overflow-y-auto p-6 sm:grid-cols-2">
              <button
                v-for="object in objectCatalog"
                :key="object.code"
                class="flex items-center gap-3 rounded-2xl border border-amber-200/10 bg-white/5 p-3 text-left transition hover:border-amber-200/30"
                type="button"
                @click="applyObjectSelection(object.code)"
              >
                <span class="terrain-preview">
                  <img v-if="object.image_url" :src="object.image_url" :alt="object.name" class="terrain-preview-image terrain-preview-image-contain">
                  <span v-else :class="resolveObjectPreviewClass(object.code)" class="terrain-preview-fallback" />
                </span>
                <span class="text-sm text-amber-50">{{ object.name }}</span>
              </button>
            </div>
          </section>
        </div>
      </Teleport>

      <Teleport to="body">
        <div
          v-if="actorPickerCell !== null"
          class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/70 p-6 backdrop-blur-sm"
        >
          <div class="absolute inset-0" @click="actorPickerCell = null" />
          <section class="relative z-10 flex max-h-[min(44rem,calc(100vh-3rem))] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(17,24,39,0.98),rgba(2,6,23,0.98))] shadow-[0_30px_80px_rgba(2,6,23,0.65)]">
            <div class="flex items-start justify-between gap-4 border-b border-amber-200/10 p-6">
              <div>
                <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">NPC</p>
                <h2 class="mt-2 text-2xl text-amber-50">Клетка {{ actorPickerCell.x }},{{ actorPickerCell.y }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ describeCellState(actorPickerCell.x, actorPickerCell.y) }}</p>
              </div>
              <button class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-slate-300 transition hover:border-amber-200/30 hover:text-amber-50" type="button" @click="actorPickerCell = null">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="grid gap-3 overflow-y-auto p-6">
              <button
                v-for="actor in gameActors"
                :key="actor.id"
                class="flex items-center gap-3 rounded-2xl border border-amber-200/10 bg-white/5 p-3 text-left transition hover:border-amber-200/30"
                type="button"
                @click="applyActorSelection(actor.id)"
              >
                <img v-if="actor.image_url" :src="actor.image_url" :alt="actor.name" class="h-14 w-12 rounded-xl border border-white/10 object-cover">
                <span v-else class="flex h-14 w-12 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-sm font-semibold text-amber-100">
                  {{ actor.name.slice(0, 1) }}
                </span>
                <span class="min-w-0">
                  <span class="block truncate text-sm text-amber-50">{{ actor.name }}</span>
                  <span class="block truncate text-xs text-slate-300">{{ resolveRaceLabel(actor.race, 'Неизвестная раса') }} · {{ resolveCharacterClassLabel(actor.character_class) }}</span>
                </span>
              </button>
            </div>
          </section>
        </div>
      </Teleport>

      <SceneObjectInventoryModal
        :catalog="itemCatalog"
        :items="normalizeObjectInventory(objectInventoryTarget?.state && typeof objectInventoryTarget.state === 'object' ? (objectInventoryTarget.state as Record<string, unknown>).inventory : null)"
        :object-name="objectInventoryTarget?.name ?? objectInventoryTarget?.kind ?? ''"
        :open="objectInventoryTarget !== null"
        @close="objectInventoryTarget = null"
      />
    </template>
  </main>
</template>

<style scoped>
.scene-editor-viewport {
  position: absolute;
  inset: 0;
  overflow: hidden;
  cursor: crosshair;
  background:
    radial-gradient(circle at 20% 20%, rgba(148, 163, 184, 0.14), transparent 30%),
    linear-gradient(180deg, rgba(15, 23, 42, 0.65), rgba(2, 6, 23, 0.94));
}

.scene-editor-canvas {
  display: block;
  width: 100%;
  height: 100%;
}

.scene-hover-tooltip {
  position: absolute;
  z-index: 5;
  pointer-events: none;
  max-width: 14rem;
  border: 1px solid rgba(251, 191, 36, 0.18);
  border-radius: 999px;
  background: rgba(2, 6, 23, 0.92);
  padding: 0.45rem 0.8rem;
  font-size: 0.75rem;
  line-height: 1rem;
  color: rgb(255 248 235);
  backdrop-filter: blur(8px);
}

.scene-editor-size-widget {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 30;
  width: 12rem;
  padding: 0.75rem;
  border-radius: 1.25rem;
  border: 1px solid rgba(251, 191, 36, 0.1);
  background: linear-gradient(180deg, rgba(17, 24, 39, 0.94), rgba(2, 6, 23, 0.99));
  backdrop-filter: blur(14px);
}

.scene-editor-size-grid {
  display: grid;
  grid-template-columns: 2.25rem minmax(0, 1fr) 2.25rem;
  gap: 0.5rem;
  align-items: center;
}

.scene-editor-size-side {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  border-radius: 0.9rem;
  border: 1px solid rgba(251, 191, 36, 0.1);
  background: rgba(255, 255, 255, 0.04);
  padding: 0.35rem;
}

.scene-editor-size-side-vertical {
  flex-direction: column;
}

.scene-editor-size-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 1.9rem;
  width: 1.9rem;
  border-radius: 999px;
  border: 1px solid rgba(251, 191, 36, 0.1);
  background: rgba(2, 6, 23, 0.45);
  color: rgb(254 243 199);
}

.scene-editor-size-center {
  display: flex;
  min-height: 4.75rem;
  align-items: center;
  justify-content: center;
  border-radius: 1rem;
  border: 1px solid rgba(251, 191, 36, 0.1);
  background: rgba(2, 6, 23, 0.42);
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgb(254 243 199);
}

.terrain-preview {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 3rem;
  width: 3rem;
  flex-shrink: 0;
  overflow: hidden;
  border-radius: 0.9rem;
  border: 1px solid rgba(255, 255, 255, 0.14);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
  background: rgba(15, 23, 42, 0.35);
}

.terrain-preview-image,
.terrain-preview-fallback {
  display: block;
  height: 100%;
  width: 100%;
}

.terrain-preview-image {
  object-fit: cover;
}

.terrain-preview-image-contain {
  object-fit: contain;
  padding: 0.2rem;
}

.terrain-preview-grass {
  background:
    radial-gradient(circle at 25% 25%, rgba(238, 221, 164, 0.22), transparent 28%),
    repeating-linear-gradient(45deg, rgba(34, 197, 94, 0.18) 0 8px, rgba(21, 128, 61, 0.28) 8px 16px),
    linear-gradient(180deg, #3b7a3e, #1f5130);
}

.terrain-preview-soil {
  background:
    radial-gradient(circle at 28% 28%, rgba(255, 214, 170, 0.14), transparent 26%),
    repeating-linear-gradient(135deg, rgba(120, 53, 15, 0.16) 0 10px, rgba(146, 64, 14, 0.1) 10px 20px),
    linear-gradient(180deg, #8a5a34, #5c341a);
}

.terrain-preview-stone {
  background:
    radial-gradient(circle at 22% 20%, rgba(255, 255, 255, 0.18), transparent 22%),
    radial-gradient(circle at 72% 65%, rgba(255, 255, 255, 0.08), transparent 18%),
    repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0 10px, rgba(15, 23, 42, 0.12) 10px 20px),
    linear-gradient(180deg, #7b8796, #4c596b);
}

.terrain-preview-water {
  background:
    radial-gradient(circle at 25% 20%, rgba(255, 255, 255, 0.18), transparent 20%),
    repeating-linear-gradient(135deg, rgba(125, 211, 252, 0.12) 0 12px, rgba(14, 116, 144, 0.22) 12px 24px),
    linear-gradient(180deg, #0f6b87, #0b395b);
}

.terrain-preview-fire {
  background:
    radial-gradient(circle at 30% 70%, rgba(255, 237, 74, 0.3), transparent 22%),
    radial-gradient(circle at 60% 30%, rgba(251, 146, 60, 0.28), transparent 26%),
    repeating-linear-gradient(135deg, rgba(239, 68, 68, 0.14) 0 12px, rgba(251, 146, 60, 0.22) 12px 24px),
    linear-gradient(180deg, #9a3412, #7f1d1d);
}

.terrain-preview-poison {
  background:
    radial-gradient(circle at 68% 28%, rgba(217, 249, 157, 0.26), transparent 22%),
    radial-gradient(circle at 24% 72%, rgba(132, 204, 22, 0.18), transparent 18%),
    repeating-linear-gradient(135deg, rgba(101, 163, 13, 0.14) 0 12px, rgba(63, 98, 18, 0.2) 12px 24px),
    linear-gradient(180deg, #4d7c0f, #365314);
}

.terrain-preview-ice {
  background:
    radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.24), transparent 18%),
    repeating-linear-gradient(135deg, rgba(191, 219, 254, 0.16) 0 10px, rgba(125, 211, 252, 0.1) 10px 20px),
    linear-gradient(180deg, #93c5fd, #60a5fa 55%, #2563eb);
}

.scene-object-preview-bush {
  background:
    radial-gradient(circle at 28% 30%, rgba(220, 252, 231, 0.35), transparent 20%),
    radial-gradient(circle at 68% 36%, rgba(134, 239, 172, 0.24), transparent 24%),
    linear-gradient(180deg, #4ade80, #166534);
}

.scene-object-preview-barrel {
  background:
    linear-gradient(90deg, rgba(62, 39, 20, 0.92) 0 14%, transparent 14% 86%, rgba(62, 39, 20, 0.92) 86% 100%),
    repeating-linear-gradient(90deg, rgba(180, 111, 55, 0.92) 0 0.42rem, rgba(131, 77, 33, 0.95) 0.42rem 0.84rem),
    linear-gradient(180deg, #b45309, #78350f);
}

.scene-object-preview-house {
  background:
    linear-gradient(145deg, transparent 0 34%, #c2410c 34% 66%, transparent 66%),
    linear-gradient(180deg, #9a3412 0 42%, #7c4a2d 42% 100%);
}

@media (max-width: 1279px) {
  .scene-editor-size-widget {
    width: 10.5rem;
  }
}
</style>

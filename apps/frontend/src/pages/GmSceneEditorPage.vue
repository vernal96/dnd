<script setup lang="ts">
import { ArrowLeft, Minus, Move3D, Plus, Save } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthSession } from '@/composables/useAuthSession';
import { useToastCenter } from '@/composables/useToastCenter';
import { fetchGameActors } from '@/services/actorApi';
import { fetchSceneObjects, fetchSceneSurfaces } from '@/services/sceneCatalogApi';
import { fetchGameScene, updateGameScene } from '@/services/sceneApi';
import type { GameActor } from '@/types/actor';
import type { SceneActorPlacement, SceneCell, SceneObject, SceneObjectDefinition, SceneSurfaceDefinition, SceneViewportMetadata } from '@/types/scene';

type ToolSection = 'actors' | 'base' | 'help' | 'materials' | 'objects';
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
type HoverActorTooltip = {
  name: string;
  x: number;
  y: number;
};

const TILE_WORLD_SIZE = 112;
const ELEVATION_STEP = 12;
const MIN_CANVAS_HEIGHT = 520;
const CANVAS_BACKGROUND = '#0a1120';
const SURFACE_TEXTURES: Record<SceneSurfaceDefinition['code'], string> = {
  fire: '/scene-textures/fire.png',
  grass: '/scene-textures/grass.png',
  ice: '/scene-textures/ice.png',
  poison: '/scene-textures/poison.png',
  soil: '/scene-textures/soil.png',
  stone: '/scene-textures/stone.png',
  water: '/scene-textures/water.png',
};

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
const gameActors = ref<GameActor[]>([]);
const activeTerrain = ref<SceneSurfaceDefinition['code']>('grass');
const activeObjectKind = ref<SceneObjectDefinition['code'] | null>(null);
const activeActorId = ref<number | null>(null);
const activeEraseMode = ref(false);
const openToolSections = ref<ToolSection[]>([]);
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
  return sceneObjects.value.find((object) => object.x === x && object.y === y);
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
  sceneObjects.value = sceneObjects.value.filter((object) => !(object.x === x && object.y === y));
  sceneActorPlacements.value = sceneActorPlacements.value.filter((placement) => !(placement.x === x && placement.y === y));
}

/**
 * Переключает authored-объект на клетке.
 */
function toggleObjectAtCell(x: number, y: number): void {
  if (activeObjectKind.value === null) {
    return;
  }

  const existingObject = getObjectAtCell(x, y);

  if (existingObject && existingObject.kind === activeObjectKind.value) {
    sceneObjects.value = sceneObjects.value.filter((object) => !(object.x === x && object.y === y));
    activeObjectKind.value = null;

    return;
  }

  const objectDefinition = objectCatalog.value.find((item) => item.code === activeObjectKind.value);

  if (!objectDefinition) {
    return;
  }

  sceneObjects.value = [
    ...sceneObjects.value.filter((object) => !(object.x === x && object.y === y)),
    {
      kind: objectDefinition.code,
      name: objectDefinition.name,
      x,
      y,
      width: objectDefinition.width,
      height: objectDefinition.height,
      is_hidden: false,
      is_interactive: objectDefinition.is_interactive,
      state: null,
    },
  ];

  activeObjectKind.value = null;
}

/**
 * Переключает authored-размещение актора на клетке.
 */
function toggleActorAtCell(x: number, y: number): void {
  if (activeActorId.value === null) {
    return;
  }

  const existingPlacement = getActorPlacementAtCell(x, y);

  if (existingPlacement && existingPlacement.actor_id === activeActorId.value) {
    sceneActorPlacements.value = sceneActorPlacements.value.filter((placement) => !(placement.x === x && placement.y === y));
    activeActorId.value = null;

    return;
  }

  const actor = gameActors.value.find((item) => item.id === activeActorId.value);

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

  activeActorId.value = null;
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
 * Загружает изображение по URL и кэширует его для canvas-рендера.
 */
function resolveCachedImage(url: string | null | undefined): HTMLImageElement | null {
  if (!url) {
    return null;
  }

  const cachedImage = imageCache.get(url);

  if (cachedImage) {
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
    const [scene, surfaces, objects, actors] = await Promise.all([
      fetchGameScene(gameId.value, sceneId.value),
      fetchSceneSurfaces(),
      fetchSceneObjects(),
      fetchGameActors(),
    ]);

    surfaceCatalog.value = surfaces;
    objectCatalog.value = objects;
    gameActors.value = actors.filter((actor) => actor.kind === 'npc');
    sceneName.value = scene.scene_template.name;
    sceneDescription.value = scene.scene_template.description ?? '';
    gridWidth.value = Math.max(6, scene.scene_template.width);
    gridHeight.value = Math.max(6, scene.scene_template.height);
    sceneCells.value = buildGridCells(gridWidth.value, gridHeight.value, scene.scene_template.cells);
    sceneObjects.value = scene.scene_template.objects.filter((object): object is SceneObject => object.x !== null && object.y !== null);
    sceneActorPlacements.value = scene.scene_template.actor_placements;

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
 * Увеличивает authored-сетку на одну колонку или строку.
 */
function resizeGrid(direction: 'height' | 'width', delta: number): void {
  const nextWidth = direction === 'width' ? Math.max(6, gridWidth.value + delta) : gridWidth.value;
  const nextHeight = direction === 'height' ? Math.max(6, gridHeight.value + delta) : gridHeight.value;

  gridWidth.value = nextWidth;
  gridHeight.value = nextHeight;
  sceneCells.value = buildGridCells(nextWidth, nextHeight, sceneCells.value);
  sceneObjects.value = sceneObjects.value.filter((object) => object.x !== null && object.y !== null && object.x < nextWidth && object.y < nextHeight);
  sceneActorPlacements.value = sceneActorPlacements.value.filter((placement) => placement.x < nextWidth && placement.y < nextHeight);
}

/**
 * Переключает состояние спойлера панели инструментов.
 */
function toggleToolSection(section: ToolSection): void {
  if (openToolSections.value.includes(section)) {
    openToolSections.value = openToolSections.value.filter((value) => value !== section);

    return;
  }

  openToolSections.value = [...openToolSections.value, section];
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

  if (activeObjectKind.value !== null) {
    toggleObjectAtCell(x, y);
    return;
  }

  if (activeActorId.value !== null) {
    toggleActorAtCell(x, y);
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

    if (
      targetCell !== null
      && activeObjectKind.value === null
      && activeActorId.value === null
      && !activeEraseMode.value
      && !hasEntity
    ) {
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
function handleCanvasAuxClick(event: MouseEvent): void {
  event.preventDefault();

  const point = resolveCanvasPoint(event);
  const targetCell = point ? findCellAtCanvasPoint(point) : null;

  if (targetCell === null) {
    return;
  }

  const hasObject = getObjectAtCell(targetCell.cell.x, targetCell.cell.y) !== undefined;
  const hasActor = getActorPlacementAtCell(targetCell.cell.x, targetCell.cell.y) !== undefined;

  if (!hasObject && !hasActor) {
    return;
  }

  selectedCellKey.value = resolveCellKey(targetCell.cell.x, targetCell.cell.y);
  eraseCellContent(targetCell.cell.x, targetCell.cell.y);
  scheduleCanvasRender();
}

/**
 * Сбрасывает hover, когда указатель уходит из viewport.
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
  const textureImage = resolveCachedImage(SURFACE_TEXTURES[projectedCell.cell.terrain_type as SceneSurfaceDefinition['code']]);
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
function drawBush(context: CanvasRenderingContext2D, projectedCell: ProjectedCell, isSelected = false): void {
  const baseX = projectedCell.center.x;
  const baseY = projectedCell.center.y - 8;
  const tileWidth = projectedCell.bounds.maxX - projectedCell.bounds.minX;
  const scale = Math.max(0.72, Math.min(1.18, tileWidth / 120));

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
    context.arc(leaf.x * scale, leaf.y * scale, leaf.radius * scale, 0, Math.PI * 2);
    context.fill();

    context.lineWidth = 1.2;
    context.strokeStyle = 'rgba(236, 252, 203, 0.28)';
    context.stroke();
  }

  if (isSelected) {
    context.strokeStyle = 'rgba(250, 204, 21, 0.9)';
    context.lineWidth = 3;
    context.beginPath();
    context.ellipse(0, -22, 32 * scale, 26 * scale, 0, 0, Math.PI * 2);
    context.stroke();
  }

  context.restore();
}

/**
 * Отрисовывает бочку на конкретной клетке.
 */
function drawBarrel(context: CanvasRenderingContext2D, projectedCell: ProjectedCell, isSelected = false): void {
  const baseX = projectedCell.center.x;
  const baseY = projectedCell.center.y - 6;
  const tileWidth = projectedCell.bounds.maxX - projectedCell.bounds.minX;
  const scale = Math.max(0.76, Math.min(1.15, tileWidth / 120));
  const bodyWidth = 34 * scale;
  const bodyHeight = 50 * scale;

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
  baseGradient.addColorStop(0, '#efdbba');
  baseGradient.addColorStop(0.35, '#c69a62');
  baseGradient.addColorStop(1, '#5d381d');
  context.fillStyle = baseGradient;
  context.beginPath();
  context.ellipse(0, 0, baseWidth * 0.45, 8.5, 0, 0, Math.PI * 2);
  context.fill();

  context.strokeStyle = 'rgba(255, 240, 210, 0.45)';
  context.lineWidth = 1.4;
  context.stroke();

  context.fillStyle = '#b08a57';
  context.fillRect(-2.2, -22, 4.4, 24);
  context.fillStyle = '#f3d8a6';
  context.fillRect(-0.9, -22, 1.8, 24);

  context.restore();

  context.save();
  const cardX = baseX - (cardSize.width / 2);
  const cardY = cardTop;
  const radius = 12;
  const frameFill = context.createLinearGradient(cardX, cardY, cardX + cardSize.width, cardY + cardHeight);
  frameFill.addColorStop(0, '#f5e7c8');
  frameFill.addColorStop(0.3, '#c6975b');
  frameFill.addColorStop(0.7, '#7e5734');
  frameFill.addColorStop(1, '#f3ddb2');

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
  context.lineWidth = actor.kind === 'npc' ? 2.7 : 2.1;
  context.strokeStyle = isSelected ? '#facc15' : actor.kind === 'npc' ? '#f6d48b' : '#f8fafc';
  context.shadowBlur = 18;
  context.shadowColor = 'rgba(15, 23, 42, 0.34)';
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
  context.strokeStyle = isSelected ? 'rgba(254, 240, 138, 0.85)' : 'rgba(255, 247, 228, 0.6)';
  context.stroke();
  context.restore();
}

/**
 * Отрисовывает ghost-preview инструмента на наведенной клетке.
 */
function drawGhostPreview(context: CanvasRenderingContext2D, projectedCell: ProjectedCell): void {
  context.save();
  context.globalAlpha = 0.55;

  if (activeEraseMode.value) {
    const centerX = projectedCell.center.x;
    const centerY = projectedCell.center.y - 18;
    const size = Math.max(18, (projectedCell.bounds.maxX - projectedCell.bounds.minX) * 0.18);
    context.strokeStyle = '#fb7185';
    context.lineWidth = 4;
    context.beginPath();
    context.moveTo(centerX - size, centerY - size);
    context.lineTo(centerX + size, centerY + size);
    context.moveTo(centerX + size, centerY - size);
    context.lineTo(centerX - size, centerY + size);
    context.stroke();
    context.restore();

    return;
  }

  if (activeObjectKind.value === 'bush' && getObjectAtCell(projectedCell.cell.x, projectedCell.cell.y) === undefined) {
    drawBush(context, projectedCell);
  }

  if (activeObjectKind.value === 'barrel' && getObjectAtCell(projectedCell.cell.x, projectedCell.cell.y) === undefined) {
    drawBarrel(context, projectedCell);
  }

  if (activeActorId.value !== null) {
    const actor = gameActors.value.find((item) => item.id === activeActorId.value);

    if (actor && getActorPlacementAtCell(projectedCell.cell.x, projectedCell.cell.y) === undefined) {
      drawActor(context, projectedCell, {
        actor,
        actor_id: actor.id,
        x: projectedCell.cell.x,
        y: projectedCell.cell.y,
      });
    }
  }

  context.restore();
}

/**
 * Отрисовывает все объекты и актеров поверх клеток.
 */
function drawSceneEntities(context: CanvasRenderingContext2D, projectedCells: ProjectedCell[]): void {
  const placements = projectedCells
    .map((projectedCell) => ({
      actorPlacement: getActorPlacementAtCell(projectedCell.cell.x, projectedCell.cell.y),
      object: getObjectAtCell(projectedCell.cell.x, projectedCell.cell.y),
      projectedCell,
    }))
    .sort((left, right) => left.projectedCell.center.y - right.projectedCell.center.y);

  for (const placement of placements) {
    const isSelected = selectedCellKey.value === resolveCellKey(placement.projectedCell.cell.x, placement.projectedCell.cell.y);

    if (placement.object) {
      if (placement.object.kind === 'bush') {
        drawBush(context, placement.projectedCell, isSelected);
      }

      if (placement.object.kind === 'barrel') {
        drawBarrel(context, placement.projectedCell, isSelected);
      }
    }

    if (placement.actorPlacement) {
      drawActor(context, placement.projectedCell, placement.actorPlacement, isSelected);
    }
  }
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

  drawSceneEntities(context, projectedCells);

  if (hoveredCellKey.value !== null && (activeObjectKind.value !== null || activeActorId.value !== null || activeEraseMode.value)) {
    const hoveredProjection = projectedCells.find((projectedCell) => resolveCellKey(projectedCell.cell.x, projectedCell.cell.y) === hoveredCellKey.value);

    if (hoveredProjection) {
      drawGhostPreview(context, hoveredProjection);
    }
  }
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
    activeObjectKind,
    activeActorId,
    activeEraseMode,
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
      class="fixed left-6 right-[25.5rem] top-24 z-20 rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100 backdrop-blur"
    >
      {{ sceneError }}
    </div>

    <div
      v-else-if="isSceneLoading"
      class="fixed left-6 right-[25.5rem] top-24 z-20 rounded-[1.75rem] border border-amber-200/10 bg-white/5 px-5 py-8 text-sm text-slate-300 backdrop-blur"
    >
      Загружаем сцену...
    </div>

    <template v-else>
      <div class="scene-editor-layout">
        <section class="scene-editor-shell">
          <div class="scene-editor-hintbar">
            <span>Canvas viewport</span>
            <span>ЛКМ по материалу: paint drag</span>
            <span>ЛКМ зажать без инструмента: перемещение поля</span>
            <span>СКМ зажать: наклон и поворот</span>
            <span>Клик по клетке: материал, объект или NPC</span>
            <span>Размер: {{ gridWidth }}x{{ gridHeight }}</span>
          </div>

          <div
            ref="canvasViewportRef"
            class="scene-editor-viewport"
            @auxclick="handleCanvasAuxClick"
            @contextmenu.prevent
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
        </section>

        <aside class="scene-tools-panel">
          <div class="scene-tools-panel-scroll">
            <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <div class="flex items-center justify-between gap-3">
                <span class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Выбор</span>
                <span
                  v-if="selectedCell"
                  class="text-xs text-slate-400"
                >
                  {{ selectedCell.x }},{{ selectedCell.y }}
                </span>
              </div>

              <div class="mt-4 space-y-3">
                <div
                  v-if="!selectedCell"
                  class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-300"
                >
                  Выбери клетку, объект или NPC на поле.
                </div>

                <template v-else>
                  <div class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-300">
                    Клетка: {{ selectedCell.x }},{{ selectedCell.y }} · {{ selectedCell.terrain_type }}
                  </div>

                  <div
                    v-if="selectedSceneObject"
                    class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3"
                  >
                    <p class="text-sm text-amber-50">{{ selectedSceneObject.name || selectedSceneObject.kind }}</p>
                    <p class="mt-1 text-xs text-slate-300">
                      Объект · {{ selectedSceneObject.kind }}
                    </p>
                  </div>

                  <div
                    v-if="selectedActorPlacement"
                    class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3"
                  >
                    <p class="text-sm text-amber-50">{{ selectedActorPlacement.actor.name }}</p>
                    <p class="mt-1 text-xs text-slate-300">
                      NPC · {{ selectedActorPlacement.actor.race || 'Без расы' }} · {{ selectedActorPlacement.actor.character_class || 'Без класса' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                      Ур. {{ selectedActorPlacement.actor.level }} · HP {{ selectedActorPlacement.actor.base_health ?? selectedActorPlacement.actor.health_max ?? 0 }} · {{ selectedActorPlacement.actor.movement_speed }} кл.
                    </p>
                  </div>

                  <div
                    v-if="selectedSceneObject || selectedActorPlacement"
                    class="rounded-2xl border border-rose-300/15 bg-rose-500/10 px-4 py-3 text-xs leading-5 text-rose-100"
                  >
                    `Delete` или `Backspace`, либо ПКМ по занятой клетке удаляет выбранную сущность.
                  </div>
                </template>
              </div>
            </section>

            <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <button
                class="flex w-full items-center justify-between gap-3 text-left"
                type="button"
                @click="toggleToolSection('base')"
              >
                <span class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Базовое</span>
                <span class="text-lg text-amber-100/70">{{ openToolSections.includes('base') ? '−' : '+' }}</span>
              </button>

              <div
                v-if="openToolSections.includes('base')"
                class="mt-4 space-y-4"
              >
                <label class="block">
                  <span class="text-xs uppercase text-amber-200/50">Название сцены</span>
                  <input
                    v-model="sceneName"
                    class="mt-2 w-full rounded-2xl border border-amber-200/10 bg-slate-950/50 px-4 py-3 text-sm text-amber-50 outline-none transition focus:border-amber-300/30"
                    maxlength="120"
                    type="text"
                  >
                </label>

                <label class="block">
                  <span class="text-xs uppercase text-amber-200/50">Описание</span>
                  <textarea
                    v-model="sceneDescription"
                    class="mt-2 min-h-28 w-full rounded-2xl border border-amber-200/10 bg-slate-950/50 px-4 py-3 text-sm text-amber-50 outline-none transition focus:border-amber-300/30"
                    maxlength="1000"
                  />
                </label>

                <div class="rounded-2xl border border-amber-200/10 bg-white/5 p-4">
                  <p class="text-xs uppercase text-amber-200/50">
                    Размер поля
                  </p>
                  <div class="mt-3 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-sm text-slate-300">Ширина</span>
                      <div class="flex items-center gap-2">
                        <button
                          class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-amber-50"
                          type="button"
                          @click="resizeGrid('width', -1)"
                        >
                          <Minus class="h-4 w-4" />
                        </button>
                        <span class="min-w-10 text-center text-sm text-amber-50">{{ gridWidth }}</span>
                        <button
                          class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-amber-50"
                          type="button"
                          @click="resizeGrid('width', 1)"
                        >
                          <Plus class="h-4 w-4" />
                        </button>
                      </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                      <span class="text-sm text-slate-300">Высота</span>
                      <div class="flex items-center gap-2">
                        <button
                          class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-amber-50"
                          type="button"
                          @click="resizeGrid('height', -1)"
                        >
                          <Minus class="h-4 w-4" />
                        </button>
                        <span class="min-w-10 text-center text-sm text-amber-50">{{ gridHeight }}</span>
                        <button
                          class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-amber-50"
                          type="button"
                          @click="resizeGrid('height', 1)"
                        >
                          <Plus class="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <button
                class="flex w-full items-center justify-between gap-3 text-left"
                type="button"
                @click="toggleToolSection('materials')"
              >
                <span class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Материалы</span>
                <span class="text-lg text-amber-100/70">{{ openToolSections.includes('materials') ? '−' : '+' }}</span>
              </button>

              <div
                v-if="openToolSections.includes('materials')"
                class="mt-4 space-y-4"
              >
                <button
                  :class="activeEraseMode ? 'border-rose-300/35 bg-rose-500/10 text-rose-100' : 'border-amber-200/10 bg-white/5 text-slate-200'"
                  class="flex w-full items-center justify-center rounded-2xl border px-4 py-3 text-sm transition hover:border-rose-300/35"
                  type="button"
                  @click="activeEraseMode = !activeEraseMode; activeObjectKind = null; activeActorId = null"
                >
                  {{ activeEraseMode ? 'Удаление включено' : 'Режим удаления' }}
                </button>

                <div class="grid gap-3">
                  <button
                    v-for="surface in surfaceCatalog"
                    :key="surface.code"
                    :class="activeTerrain === surface.code ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-white/5'"
                    class="flex items-center gap-3 rounded-2xl border p-3 text-left transition hover:border-amber-200/30"
                    type="button"
                    @click="activeTerrain = surface.code; activeObjectKind = null; activeActorId = null; activeEraseMode = false"
                  >
                    <span :class="resolveSurfacePreviewClass(surface.code)" class="terrain-preview" />
                    <span class="text-sm text-amber-50">{{ surface.name }}</span>
                  </button>
                </div>
              </div>
            </section>

            <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <button
                class="flex w-full items-center justify-between gap-3 text-left"
                type="button"
                @click="toggleToolSection('objects')"
              >
                <span class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Объекты</span>
                <span class="text-lg text-amber-100/70">{{ openToolSections.includes('objects') ? '−' : '+' }}</span>
              </button>

              <div
                v-if="openToolSections.includes('objects')"
                class="mt-4 space-y-4"
              >
                <div class="grid gap-3">
                  <button
                    v-for="object in objectCatalog"
                    :key="object.code"
                    :class="activeObjectKind === object.code ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-white/5'"
                    class="flex items-center gap-3 rounded-2xl border p-3 text-left transition hover:border-amber-200/30"
                    type="button"
                    @click="activeObjectKind = activeObjectKind === object.code ? null : object.code; activeActorId = null; activeEraseMode = false"
                  >
                    <span :class="resolveObjectPreviewClass(object.code)" class="terrain-preview" />
                    <span class="text-sm text-amber-50">{{ object.name }}</span>
                  </button>
                </div>

                <p class="text-sm leading-6 text-slate-300">
                  На одной клетке может находиться только один объект. Повторный клик по клетке тем же объектом убирает его.
                </p>
              </div>
            </section>

            <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <button
                class="flex w-full items-center justify-between gap-3 text-left"
                type="button"
                @click="toggleToolSection('actors')"
              >
                <span class="text-xs uppercase tracking-[0.2em] text-amber-200/50">NPC</span>
                <span class="text-lg text-amber-100/70">{{ openToolSections.includes('actors') ? '−' : '+' }}</span>
              </button>

              <div
                v-if="openToolSections.includes('actors')"
                class="mt-4 space-y-4"
              >
                <div
                  v-if="gameActors.length === 0"
                  class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-300"
                >
                  В игре пока нет NPC.
                </div>

                <div
                  v-else
                  class="grid gap-3"
                >
                  <button
                    v-for="actor in gameActors"
                    :key="actor.id"
                    :class="activeActorId === actor.id ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-white/5'"
                    class="flex items-center gap-3 rounded-2xl border p-3 text-left transition hover:border-amber-200/30"
                    type="button"
                    @click="activeActorId = activeActorId === actor.id ? null : actor.id; activeObjectKind = null; activeEraseMode = false"
                  >
                    <img
                      v-if="actor.image_url"
                      :src="actor.image_url"
                      :alt="actor.name"
                      class="h-14 w-12 rounded-xl border border-white/10 object-cover"
                    >
                    <span
                      v-else
                      class="flex h-14 w-12 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-sm font-semibold text-amber-100"
                    >
                      {{ actor.name.slice(0, 1) }}
                    </span>
                    <span class="min-w-0">
                      <span class="block truncate text-sm text-amber-50">{{ actor.name }}</span>
                      <span class="block truncate text-xs text-slate-300">
                        {{ actor.race || 'Неизвестная раса' }} · {{ actor.character_class || 'Без класса' }}
                      </span>
                      <span class="block text-xs text-slate-400">
                        Ур. {{ actor.level }} · HP {{ actor.base_health ?? actor.health_max ?? 0 }} · {{ actor.movement_speed }} кл.
                      </span>
                    </span>
                  </button>
                </div>
              </div>
            </section>

            <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <button
                class="flex w-full items-center justify-between gap-3 text-left"
                type="button"
                @click="toggleToolSection('help')"
              >
                <span class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Навигация</span>
                <span class="text-lg text-amber-100/70">{{ openToolSections.includes('help') ? '−' : '+' }}</span>
              </button>

              <div
                v-if="openToolSections.includes('help')"
                class="mt-4 flex items-start gap-3"
              >
                <div class="rounded-2xl border border-amber-200/10 bg-white/5 p-2.5 text-amber-100">
                  <Move3D class="h-5 w-5" />
                </div>
                <div class="text-sm leading-6 text-slate-300">
                  Редактор теперь рисуется через canvas. Для материалов доступна paint-drag покраска клеток. Зажатая левая кнопка без активного объекта, NPC или удаления двигает сцену. Зажатая средняя кнопка меняет угол обзора.
                </div>
              </div>
            </section>
          </div>
        </aside>
      </div>
    </template>
  </main>
</template>

<style scoped>
.scene-editor-layout {
  position: absolute;
  inset: 0;
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem;
}

.scene-editor-shell {
  position: relative;
  flex: 1 1 auto;
  min-width: 0;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding-top: 5rem;
}

.scene-editor-hintbar {
  margin-bottom: 1rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: rgb(203 213 225);
}

.scene-editor-viewport {
  position: relative;
  flex: 1 1 auto;
  overflow: hidden;
  cursor: crosshair;
  border-radius: 1.5rem;
  border: 1px solid rgba(251, 191, 36, 0.12);
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

.scene-tools-panel {
  position: relative;
  z-index: 10;
  width: 24rem;
  min-width: 24rem;
  height: 100%;
  overflow: hidden;
  border-left: 1px solid rgba(251, 191, 36, 0.1);
  border-radius: 1.75rem;
  background: linear-gradient(180deg, rgba(17, 24, 39, 0.94), rgba(2, 6, 23, 0.99));
}

.scene-tools-panel-scroll {
  height: 100%;
  overflow-y: auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.terrain-preview {
  display: inline-block;
  height: 3rem;
  width: 3rem;
  flex-shrink: 0;
  border-radius: 0.9rem;
  border: 1px solid rgba(255, 255, 255, 0.14);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
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

@media (max-width: 1279px) {
  .scene-tools-panel {
    width: 22rem;
    min-width: 22rem;
  }
}
</style>

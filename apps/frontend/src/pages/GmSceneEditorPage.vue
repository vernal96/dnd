<script setup lang="ts">
import { ArrowLeft, Minus, Move3D, Plus, Save } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthSession } from '@/composables/useAuthSession';
import { useToastCenter } from '@/composables/useToastCenter';
import { fetchGameActors } from '@/services/actorApi';
import { fetchSceneObjects, fetchSceneSurfaces } from '@/services/sceneCatalogApi';
import { fetchGameScene, updateGameScene } from '@/services/sceneApi';
import type { GameActor } from '@/types/actor';
import type { SceneActorPlacement, SceneCell, SceneObject, SceneObjectDefinition, SceneSurfaceDefinition, SceneViewportMetadata } from '@/types/scene';

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
const openToolSections = ref<Array<'actors' | 'base' | 'help' | 'materials' | 'objects'>>([]);
const viewport = ref<SceneViewportMetadata>({
  offsetX: 0,
  offsetY: 0,
  rotateX: 45,
  rotateZ: -45,
});
const isViewportDragging = ref(false);
const isViewportRotating = ref(false);
const hasViewportMoved = ref(false);
const viewportPointerMode = ref<'pan' | 'rotate' | null>(null);
const viewportPointerStartX = ref(0);
const viewportPointerStartY = ref(0);

const gameId = computed<number | null>(() => parseRouteParam(route.params.id));
const sceneId = computed<number | null>(() => parseRouteParam(route.params.sceneId));
const sceneBackLink = computed<string>(() => (gameId.value === null ? '/cabinet/gm' : `/cabinet/gm/games/${gameId.value}`));

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
 * Возвращает CSS-класс для authored-клетки по поверхности.
 */
function resolveTerrainClass(terrainType: string): string {
  return `terrain-tile terrain-tile-${terrainType}`;
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
 * Возвращает CSS-класс визуализации объекта на клетке.
 */
function resolveObjectTokenClass(kind: SceneObject['kind']): string {
  return `scene-object-token scene-object-token-${kind}`;
}

/**
 * Возвращает CSS-класс визуализации карточки актора.
 */
function resolveActorCardClass(actor: GameActor): string {
  return actor.kind === 'npc' ? 'scene-actor-card scene-actor-card-npc' : 'scene-actor-card';
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
      rotateZ: typeof savedViewport?.rotateZ === 'number' ? savedViewport.rotateZ : -45,
    };
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
 * Обрабатывает нажатие мыши в области viewport.
 */
function handleViewportPointerDown(event: MouseEvent): void {
  hasViewportMoved.value = false;
  viewportPointerStartX.value = event.clientX;
  viewportPointerStartY.value = event.clientY;

  if (event.button === 0) {
    viewportPointerMode.value = 'pan';
    event.preventDefault();
  }

  if (event.button === 1) {
    viewportPointerMode.value = 'rotate';
    event.preventDefault();
  }
}

/**
 * Обрабатывает обычный клик по authored-клетке.
 */
function handleCellClick(x: number, y: number): void {
  if (hasViewportMoved.value) {
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

  paintCell(x, y);
}

/**
 * Переключает состояние спойлера панели инструментов.
 */
function toggleToolSection(section: 'actors' | 'base' | 'help' | 'materials' | 'objects'): void {
  if (openToolSections.value.includes(section)) {
    openToolSections.value = openToolSections.value.filter((value) => value !== section);

    return;
  }

  openToolSections.value = [...openToolSections.value, section];
}

/**
 * Сдвигает сцену или меняет угол обзора мышью.
 */
function handleGlobalMouseMove(event: MouseEvent): void {
  const deltaX = event.clientX - viewportPointerStartX.value;
  const deltaY = event.clientY - viewportPointerStartY.value;
  const passedThreshold = Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4;

  if (viewportPointerMode.value === 'pan' && passedThreshold) {
    isViewportDragging.value = true;
  }

  if (viewportPointerMode.value === 'rotate' && passedThreshold) {
    isViewportRotating.value = true;
  }

  if (isViewportDragging.value) {
    hasViewportMoved.value = true;
    viewport.value = {
      ...viewport.value,
      offsetX: viewport.value.offsetX + event.movementX,
      offsetY: viewport.value.offsetY + event.movementY,
    };
  }

  if (isViewportRotating.value) {
    hasViewportMoved.value = true;
    viewport.value = {
      ...viewport.value,
      rotateX: Math.min(78, Math.max(28, viewport.value.rotateX - event.movementY * 0.35)),
      rotateZ: Math.min(-8, Math.max(-78, viewport.value.rotateZ + event.movementX * 0.45)),
    };
  }
}

/**
 * Завершает режимы перемещения и поворота.
 */
function handleGlobalMouseUp(): void {
  isViewportDragging.value = false;
  isViewportRotating.value = false;
  viewportPointerMode.value = null;
}

onMounted(async () => {
  window.addEventListener('mousemove', handleGlobalMouseMove);
  window.addEventListener('mouseup', handleGlobalMouseUp);

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
});

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', handleGlobalMouseMove);
  window.removeEventListener('mouseup', handleGlobalMouseUp);
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
            <span>ЛКМ зажать: перемещение поля</span>
            <span>СКМ зажать: наклон и поворот</span>
            <span>Клик по клетке: материал, объект или NPC</span>
            <span>Размер: {{ gridWidth }}x{{ gridHeight }}</span>
          </div>

          <div
            class="scene-editor-viewport"
            @mousedown="handleViewportPointerDown"
          >
            <div
              class="scene-editor-stage"
              :style="{
                transform: `translate3d(${viewport.offsetX}px, ${viewport.offsetY}px, 0) rotateX(${viewport.rotateX}deg) rotateZ(${viewport.rotateZ}deg)`,
                gridTemplateColumns: `repeat(${gridWidth}, minmax(0, 6.5rem))`,
              }"
            >
              <button
                v-for="cell in sceneCells"
                :key="`${cell.x}-${cell.y}`"
                :class="resolveTerrainClass(cell.terrain_type)"
                class="scene-editor-tile"
                :title="getActorPlacementAtCell(cell.x, cell.y)?.actor.name ?? ''"
                type="button"
                @click="handleCellClick(cell.x, cell.y)"
              >
                <div
                  v-if="getObjectAtCell(cell.x, cell.y)"
                  class="scene-object-anchor"
                >
                  <span class="scene-object-pivot">
                    <span :class="resolveObjectTokenClass(getObjectAtCell(cell.x, cell.y)!.kind)">
                      <template v-if="getObjectAtCell(cell.x, cell.y)!.kind === 'bush'">
                        <span class="scene-object-bush-foliage scene-object-bush-foliage-back" />
                        <span class="scene-object-bush-foliage scene-object-bush-foliage-left" />
                        <span class="scene-object-bush-foliage scene-object-bush-foliage-right" />
                        <span class="scene-object-bush-foliage scene-object-bush-foliage-front" />
                      </template>

                      <template v-if="getObjectAtCell(cell.x, cell.y)!.kind === 'barrel'">
                        <span class="scene-object-barrel-top" />
                        <span class="scene-object-barrel-body scene-object-barrel-body-front" />
                        <span class="scene-object-barrel-body scene-object-barrel-body-back" />
                        <span class="scene-object-barrel-side scene-object-barrel-side-left" />
                        <span class="scene-object-barrel-side scene-object-barrel-side-right" />
                      </template>
                    </span>
                  </span>
                </div>

                <div
                  v-if="getActorPlacementAtCell(cell.x, cell.y)"
                  class="scene-actor-anchor"
                >
                  <span class="scene-actor-pivot">
                    <span :class="resolveActorCardClass(getActorPlacementAtCell(cell.x, cell.y)!.actor)">
                      <span class="scene-actor-base" />
                      <span class="scene-actor-stand" />
                      <span class="scene-actor-card-face">
                        <img
                          v-if="getActorPlacementAtCell(cell.x, cell.y)!.actor.image_url"
                          :src="getActorPlacementAtCell(cell.x, cell.y)!.actor.image_url ?? ''"
                          :alt="getActorPlacementAtCell(cell.x, cell.y)!.actor.name"
                          class="scene-actor-portrait"
                        >
                        <span
                          v-else
                          class="scene-actor-portrait scene-actor-portrait-placeholder"
                        >
                          {{ getActorPlacementAtCell(cell.x, cell.y)!.actor.name.slice(0, 1) }}
                        </span>
                      </span>
                    </span>
                  </span>
                </div>
                <span class="pointer-events-none text-xs font-medium text-white/75">
                  {{ cell.x }},{{ cell.y }}
                </span>
              </button>
            </div>
          </div>
        </section>

        <aside class="scene-tools-panel">
          <div class="scene-tools-panel-scroll">
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
              <div class="grid gap-3">
                <button
                  v-for="surface in surfaceCatalog"
                  :key="surface.code"
                  :class="activeTerrain === surface.code ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-white/5'"
                  class="flex items-center gap-3 rounded-2xl border p-3 text-left transition hover:border-amber-200/30"
                  type="button"
                  @click="activeTerrain = surface.code; activeObjectKind = null; activeActorId = null"
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
                  @click="activeObjectKind = activeObjectKind === object.code ? null : object.code; activeActorId = null"
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
                  @click="activeActorId = activeActorId === actor.id ? null : actor.id; activeObjectKind = null"
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
                Зажатая левая кнопка двигает сцену. Зажатая средняя кнопка меняет угол обзора. Обычный клик по клетке применяет материал, ставит объект или размещает выбранного NPC.
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
  flex: 1 1 auto;
  overflow: hidden;
  perspective: 1600px;
  cursor: crosshair;
  border-radius: 1.5rem;
  border: 1px solid rgba(251, 191, 36, 0.12);
  background:
    radial-gradient(circle at 20% 20%, rgba(148, 163, 184, 0.14), transparent 30%),
    linear-gradient(180deg, rgba(15, 23, 42, 0.65), rgba(2, 6, 23, 0.94));
}

.scene-editor-stage {
  display: grid;
  width: max-content;
  gap: 0.5rem;
  pointer-events: none;
  transform-style: preserve-3d;
  transform-origin: center center;
  padding: 10rem;
}

.scene-editor-tile {
  position: relative;
  overflow: visible;
  transform-style: preserve-3d;
  pointer-events: auto;
  cursor: crosshair;
  height: 6.5rem;
  width: 6.5rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 1.2rem;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 18px 30px rgba(15, 23, 42, 0.35);
  transition: transform 120ms ease, filter 120ms ease;
}

.scene-editor-tile:hover {
  filter: brightness(1.06);
  transform: translateY(-2px);
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

.terrain-preview-grass,
.terrain-tile-grass {
  background:
    radial-gradient(circle at 25% 25%, rgba(238, 221, 164, 0.22), transparent 28%),
    repeating-linear-gradient(45deg, rgba(34, 197, 94, 0.18) 0 8px, rgba(21, 128, 61, 0.28) 8px 16px),
    linear-gradient(180deg, #3b7a3e, #1f5130);
}

.terrain-preview-soil,
.terrain-tile-soil {
  background:
    radial-gradient(circle at 28% 28%, rgba(255, 214, 170, 0.14), transparent 26%),
    repeating-linear-gradient(135deg, rgba(120, 53, 15, 0.16) 0 10px, rgba(146, 64, 14, 0.1) 10px 20px),
    linear-gradient(180deg, #8a5a34, #5c341a);
}

.terrain-preview-stone,
.terrain-tile-stone {
  background:
    radial-gradient(circle at 22% 20%, rgba(255, 255, 255, 0.18), transparent 22%),
    radial-gradient(circle at 72% 65%, rgba(255, 255, 255, 0.08), transparent 18%),
    repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0 10px, rgba(15, 23, 42, 0.12) 10px 20px),
    linear-gradient(180deg, #7b8796, #4c596b);
}

.terrain-preview-water,
.terrain-tile-water {
  background:
    radial-gradient(circle at 25% 20%, rgba(255, 255, 255, 0.18), transparent 20%),
    repeating-linear-gradient(135deg, rgba(125, 211, 252, 0.12) 0 12px, rgba(14, 116, 144, 0.22) 12px 24px),
    linear-gradient(180deg, #0f6b87, #0b395b);
}

.terrain-preview-fire,
.terrain-tile-fire {
  background:
    radial-gradient(circle at 30% 70%, rgba(255, 237, 74, 0.3), transparent 22%),
    radial-gradient(circle at 60% 30%, rgba(251, 146, 60, 0.28), transparent 26%),
    repeating-linear-gradient(135deg, rgba(239, 68, 68, 0.14) 0 12px, rgba(251, 146, 60, 0.22) 12px 24px),
    linear-gradient(180deg, #9a3412, #7f1d1d);
}

.terrain-preview-poison,
.terrain-tile-poison {
  background:
    radial-gradient(circle at 68% 28%, rgba(217, 249, 157, 0.26), transparent 22%),
    radial-gradient(circle at 24% 72%, rgba(132, 204, 22, 0.18), transparent 18%),
    repeating-linear-gradient(135deg, rgba(101, 163, 13, 0.14) 0 12px, rgba(63, 98, 18, 0.2) 12px 24px),
    linear-gradient(180deg, #4d7c0f, #365314);
}

.terrain-preview-ice,
.terrain-tile-ice {
  background:
    radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.24), transparent 18%),
    repeating-linear-gradient(135deg, rgba(191, 219, 254, 0.16) 0 10px, rgba(125, 211, 252, 0.1) 10px 20px),
    linear-gradient(180deg, #93c5fd, #60a5fa 55%, #2563eb);
}

.scene-object-token {
  --scene-object-depth: 0.9375rem;
  position: absolute;
  left: 0;
  top: 0;
  display: block;
  z-index: 1;
  transform-origin: center bottom;
  pointer-events: none;
  transform-style: preserve-3d;
  backface-visibility: hidden;
  filter: drop-shadow(0 0.7rem 0.8rem rgba(15, 23, 42, 0.35));
}

.scene-object-anchor {
  position: absolute;
  inset: 0;
  transform-style: preserve-3d;
  pointer-events: none;
}

.scene-actor-anchor {
  position: absolute;
  inset: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  transform-style: preserve-3d;
  pointer-events: none;
}

.scene-object-pivot {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 0;
  height: 0;
  transform: translate3d(-50%, -50%, 0.16rem);
  transform-style: preserve-3d;
}

.scene-actor-pivot {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 0;
  height: 0;
  transform: translate3d(-50%, -50%, 0.04rem);
  transform-style: preserve-3d;
}

.scene-object-token-bush {
  width: 7.6rem;
  height: 8.4rem;
  transform: translate(-50%, -100%) rotateX(-90deg);
}

.scene-object-token-barrel {
  width: 5.2rem;
  height: 6.6rem;
  transform: translate(-50%, -100%) rotateX(-90deg);
}

.scene-object-bush-foliage {
  position: absolute;
  left: 50%;
  width: 4.7rem;
  height: 4rem;
  transform: translateX(-50%);
  border-radius: 999px 999px 2rem 2rem;
  background:
    radial-gradient(circle at 28% 30%, rgba(220, 252, 231, 0.4), transparent 20%),
    radial-gradient(circle at 68% 36%, rgba(134, 239, 172, 0.28), transparent 24%),
    linear-gradient(180deg, #4ade80, #166534);
  box-shadow: inset 0 0.24rem 0 rgba(255, 255, 255, 0.12);
}

.scene-object-bush-foliage-back {
  bottom: 3.8rem;
  transform: translateX(-50%) translateZ(-1rem);
  filter: brightness(0.88);
}

.scene-object-bush-foliage-left {
  bottom: 2.7rem;
  transform: translateX(calc(-50% - 1.4rem)) translateZ(-0.24rem);
  filter: brightness(0.92);
}

.scene-object-bush-foliage-right {
  bottom: 2.7rem;
  transform: translateX(calc(-50% + 1.4rem)) translateZ(0.32rem);
}

.scene-object-bush-foliage-front {
  bottom: 2rem;
  width: 5.6rem;
  height: 4.4rem;
  transform: translateX(-50%) translateZ(1.04rem);
}

.scene-object-barrel-body {
  position: absolute;
  left: 50%;
  bottom: 0.4rem;
  width: 4.1rem;
  height: 5.2rem;
  background:
    linear-gradient(90deg, rgba(62, 39, 20, 0.92) 0 14%, transparent 14% 86%, rgba(62, 39, 20, 0.92) 86% 100%),
    repeating-linear-gradient(90deg, rgba(180, 111, 55, 0.92) 0 0.84rem, rgba(131, 77, 33, 0.95) 0.84rem 1.68rem),
    linear-gradient(180deg, #b45309, #78350f);
  box-shadow:
    inset 0 0.4rem 0 rgba(255, 255, 255, 0.12),
    0 0 0 0.36rem rgba(62, 39, 20, 0.88);
}

.scene-object-barrel-body-front {
  transform: translateX(-50%) translateZ(calc(var(--scene-object-depth) / 2));
  border-radius: 1.7rem;
}

.scene-object-barrel-body-back {
  transform: translateX(-50%) translateZ(calc(var(--scene-object-depth) / -2));
  border-radius: 1.7rem;
  filter: brightness(0.82);
}

.scene-object-barrel-side {
  position: absolute;
  bottom: 0.4rem;
  width: var(--scene-object-depth);
  height: 5.2rem;
  border-radius: 0.9rem;
  background:
    linear-gradient(180deg, rgba(245, 158, 11, 0.28), transparent 18%),
    linear-gradient(180deg, #92400e, #5b3418);
  box-shadow:
    inset 0 0.24rem 0 rgba(255, 255, 255, 0.08),
    0 0 0 0.16rem rgba(62, 39, 20, 0.62);
}

.scene-object-barrel-side-left {
  left: 50%;
  transform: translateX(calc(-2.05rem - (var(--scene-object-depth) / 2))) rotateY(90deg);
}

.scene-object-barrel-side-right {
  left: 50%;
  transform: translateX(calc(2.05rem - (var(--scene-object-depth) / 2))) rotateY(90deg);
}

.scene-object-barrel-top {
  position: absolute;
  left: 50%;
  bottom: 5.7rem;
  width: 4.44rem;
  height: 1.36rem;
  transform: translateX(-50%) rotateX(90deg) translateZ(0.04rem);
  border-radius: 999px;
  background:
    radial-gradient(circle at 50% 35%, rgba(255, 237, 213, 0.28), transparent 34%),
    linear-gradient(180deg, #f59e0b, #92400e);
  box-shadow: 0 0 0 0.24rem rgba(62, 39, 20, 0.82);
}

.scene-actor-card {
  position: absolute;
  left: 0;
  top: 0;
  width: calc(6.5rem - 10px);
  height: 9rem;
  transform-origin: center bottom;
  transform: translate(-50%, -94%) rotateX(-90deg);
  transform-style: preserve-3d;
  pointer-events: none;
  filter: drop-shadow(0 1rem 1rem rgba(15, 23, 42, 0.38));
}

.scene-actor-card-npc .scene-actor-card-face {
  border-color: rgba(251, 191, 36, 0.32);
}

.scene-actor-base {
  position: absolute;
  left: 50%;
  bottom: 0;
  width: calc(6.5rem - 14px);
  height: 0.85rem;
  transform: translateX(-50%) rotateX(90deg);
  border-radius: 999px;
  background:
    radial-gradient(circle at 50% 40%, rgba(255, 237, 213, 0.28), transparent 36%),
    linear-gradient(180deg, #8b5e34, #5b3418);
  box-shadow:
    inset 0 0.16rem 0 rgba(255, 255, 255, 0.12),
    0 0 0 0.16rem rgba(69, 26, 3, 0.55);
}

.scene-actor-stand {
  position: absolute;
  left: 50%;
  bottom: 0.28rem;
  width: 0.38rem;
  height: 1rem;
  transform: translateX(-50%);
  border-radius: 999px;
  background: linear-gradient(180deg, #d6b37a, #8b5e34);
}

.scene-actor-card-face {
  position: absolute;
  left: 50%;
  bottom: 1.05rem;
  width: calc(6.5rem - 10px);
  height: 8rem;
  transform: translateX(-50%) translateZ(0.18rem);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(255, 248, 220, 0.4);
  border-radius: 0.85rem;
  background:
    linear-gradient(180deg, rgba(255, 251, 235, 0.18), rgba(255, 251, 235, 0.02) 18%, transparent 28%),
    linear-gradient(180deg, #31263f, #140f20);
  box-shadow:
    inset 0 0.1rem 0 rgba(255, 255, 255, 0.08),
    0 0 0 0.16rem rgba(120, 82, 33, 0.45),
    0 0.55rem 1rem rgba(15, 23, 42, 0.22);
}

.scene-actor-portrait {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top center;
  background: linear-gradient(180deg, rgba(251, 191, 36, 0.18), rgba(255, 255, 255, 0.04));
}

.scene-actor-portrait-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 700;
  color: rgb(254 243 199);
}

.scene-actor-name {
  display: none;
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

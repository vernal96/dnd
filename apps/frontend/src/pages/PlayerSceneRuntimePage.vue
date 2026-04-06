<script setup lang="ts">
import { ArrowLeft, Shield, Sword } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import RuntimeActorInventoryModal from '@/components/runtime/RuntimeActorInventoryModal.vue';
import RuntimeSceneCanvas from '@/components/runtime/RuntimeSceneCanvas.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import { connectRealtime, subscribeRealtime } from '@/composables/useRealtimeSocket';
import { useToastCenter } from '@/composables/useToastCenter';
import { fetchItems } from '@/services/itemApi';
import { fetchSceneObjects, fetchSceneSurfaces } from '@/services/sceneCatalogApi';
import {
  endPlayerRuntimeTurn,
  fetchPlayerActiveRuntimeScene,
  movePlayerRuntimeActor,
  usePlayerRuntimeAction,
  usePlayerRuntimeBonusAction,
} from '@/services/runtimeSceneApi';
import type { CatalogItem } from '@/types/item';
import type { RealtimeEventMessage } from '@/types/realtime';
import type { SceneObjectDefinition, SceneSurfaceDefinition } from '@/types/scene';
import type { RuntimeActorInstance, RuntimeActorInventoryItem, RuntimeEncounterParticipant, RuntimeSceneDetail } from '@/types/runtimeScene';

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
  cell: RuntimeSceneDetail['scene_template']['cells'][number];
  center: CanvasPoint;
  corners: CanvasPoint[];
};

type PointerMode = 'pan' | 'rotate' | null;

type HoverActorTooltip = {
  name: string;
  x: number;
  y: number;
};

type MovementAnimation = {
  actorId: number;
  fromX: number;
  fromY: number;
  progress: number;
  startedAt: number;
  toX: number;
  toY: number;
};

const TILE_WORLD_SIZE = 112;
const ELEVATION_STEP = 12;
const MIN_CANVAS_HEIGHT = 520;
const CANVAS_BACKGROUND = '#0a1120';
const ANIMATION_DURATION = 360;

const route = useRoute();
const router = useRouter();
const { currentUser, ensureSessionLoaded, isAuthenticated, isPending } = useAuthSession();
const { pushToast } = useToastCenter();
const isCanvasOnlyMode = true;

const runtimeScene = ref<RuntimeSceneDetail | null>(null);
const runtimeError = ref('');
const isRuntimeLoading = ref(false);
const isActorMoving = ref(false);
const itemCatalog = ref<CatalogItem[]>([]);
const objectCatalog = ref<SceneObjectDefinition[]>([]);
const surfaceCatalog = ref<SceneSurfaceDefinition[]>([]);
const inventoryActorId = ref<number | null>(null);
const selectedActorId = ref<number | null>(null);
const selectedCellKey = ref<string | null>(null);
const hoveredCellKey = ref<string | null>(null);
const hoveredActorTooltip = ref<HoverActorTooltip | null>(null);
const viewport = ref({
  offsetX: 0,
  offsetY: 0,
  rotateX: 45,
  rotateZ: 45,
  zoom: 1,
});

const canvasViewportRef = ref<HTMLDivElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
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
const pointerStartRotateZ = ref(45);
const hasMoved = ref(false);
const renderFrameId = ref<number | null>(null);
const movementAnimations = ref<Record<number, MovementAnimation>>({});
const animationFrameId = ref<number | null>(null);
const isReconnectPending = ref(false);
const isClientReady = ref(false);
const isEncounterUpdating = ref(false);

let resizeObserver: ResizeObserver | null = null;

const imageCache = new Map<string, HTMLImageElement>();
const brokenImageUrls = new Set<string>();
const loadingImageUrls = new Set<string>();
const actorContextMenu = ref<{ actorId: number; x: number; y: number } | null>(null);

const gameId = computed<number | null>(() => {
  const rawValue = route.params.id;
  const parsedValue = Number.parseInt(Array.isArray(rawValue) ? rawValue[0] : String(rawValue), 10);

  return Number.isNaN(parsedValue) ? null : parsedValue;
});
const sceneTemplate = computed(() => runtimeScene.value?.scene_template ?? null);
const sceneName = computed(() => sceneTemplate.value?.name ?? 'Сцена игрока');
const gridWidth = computed(() => sceneTemplate.value?.width ?? 6);
const gridHeight = computed(() => sceneTemplate.value?.height ?? 6);
const activeEncounter = computed(() => runtimeScene.value?.encounter ?? null);
const encounterParticipants = computed<RuntimeEncounterParticipant[]>(() => activeEncounter.value?.participants ?? []);
const currentEncounterParticipant = computed<RuntimeEncounterParticipant | null>(() => {
  if (activeEncounter.value === null) {
    return null;
  }

  return encounterParticipants.value.find((participant) => participant.id === activeEncounter.value?.current_participant_id) ?? null;
});
const currentEncounterActor = computed<RuntimeActorInstance | null>(() => currentEncounterParticipant.value?.actor ?? null);
const controlledActors = computed(() =>
  (runtimeScene.value?.actor_instances ?? []).filter((actor) => actor.controlled_by_user_id === currentUser.value?.id),
);
const selectedActor = computed<RuntimeActorInstance | null>(() => {
  if (selectedActorId.value === null) {
    return controlledActors.value[0] ?? null;
  }

  return runtimeScene.value?.actor_instances.find((actor) => actor.id === selectedActorId.value) ?? controlledActors.value[0] ?? null;
});
const inventoryActor = computed<RuntimeActorInstance | null>(() => {
  if (inventoryActorId.value === null) {
    return null;
  }

  return runtimeScene.value?.actor_instances.find((actor) => actor.id === inventoryActorId.value) ?? null;
});
const selectedEncounterParticipant = computed<RuntimeEncounterParticipant | null>(() => {
  if (selectedActor.value === null) {
    return null;
  }

  return encounterParticipants.value.find((participant) => participant.actor_id === selectedActor.value?.id) ?? null;
});
const currentControlledEncounterParticipant = computed<RuntimeEncounterParticipant | null>(() => {
  const participant = currentEncounterParticipant.value;
  const actor = participant?.actor;

  if (!participant || !actor || actor.controlled_by_user_id !== currentUser.value?.id) {
    return null;
  }

  return participant;
});

function resolveCellKey(x: number, y: number): string {
  return `${x}-${y}`;
}

function prettifyItemCode(itemCode: string): string {
  return itemCode
    .split('-')
    .filter((part) => part !== '')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function normalizeInventory(inventory: RuntimeActorInventoryItem[] | null | undefined): RuntimeActorInventoryItem[] {
  return Array.isArray(inventory) ? inventory : [];
}

function closeActorContextMenu(): void {
  actorContextMenu.value = null;
}

function canOpenInventory(actor: RuntimeActorInstance): boolean {
  return actor.controlled_by_user_id === currentUser.value?.id;
}

function canOpenInventoryForActorId(actorId: number): boolean {
  const actor = getRuntimeActorById(actorId);

  return actor ? canOpenInventory(actor) : false;
}

function openInventoryForActor(actorId: number): void {
  inventoryActorId.value = actorId;
  closeActorContextMenu();
}

function syncEncounterSelection(): void {
  if (currentControlledEncounterParticipant.value?.actor) {
    selectedActorId.value = currentControlledEncounterParticipant.value.actor.id;
    return;
  }

  selectedActorId.value = selectedActorId.value ?? controlledActors.value[0]?.id ?? null;
}

function getActorAtCell(x: number, y: number): RuntimeActorInstance | undefined {
  return runtimeScene.value?.actor_instances.find((actor) => actor.x === x && actor.y === y);
}

function getRuntimeActorById(actorId: number): RuntimeActorInstance | null {
  return runtimeScene.value?.actor_instances.find((actor) => actor.id === actorId) ?? null;
}

function getObjectAtCell(x: number, y: number): RuntimeSceneDetail['scene_template']['objects'][number] | undefined {
  return sceneTemplate.value?.objects.find((object) => object.x === x && object.y === y);
}

function getItemDropAtCell(x: number, y: number): RuntimeSceneDetail['item_drops'][number] | undefined {
  return runtimeScene.value?.item_drops.find((item) => item.x === x && item.y === y);
}

function canActorMoveNow(actor: RuntimeActorInstance): boolean {
  if (activeEncounter.value === null) {
    return true;
  }

  return currentControlledEncounterParticipant.value?.actor_id === actor.id;
}

function resolveEncounterDistance(actor: RuntimeActorInstance, x: number, y: number): number {
  if (actor.x === null || actor.y === null) {
    return 0;
  }

  return Math.abs(actor.x - x) + Math.abs(actor.y - y);
}

function applyLocalEncounterMovement(actorId: number, updatedActor: RuntimeActorInstance, distance: number): void {
  if (runtimeScene.value?.encounter === null) {
    return;
  }

  const participant = runtimeScene.value.encounter.participants.find((item) => item.actor_id === actorId);

  if (!participant) {
    return;
  }

  participant.actor = updatedActor;
  participant.movement_left = Math.max(0, (participant.movement_left ?? 0) - distance);
}

function resolveSurfacePalette(code: string): { accent: string; fill: string; shadow: string; stroke: string } {
  switch (code) {
    case 'stone':
      return { accent: '#d5dbe7', fill: '#7f8aa0', shadow: '#465166', stroke: 'rgba(224,231,255,0.26)' };
    case 'soil':
      return { accent: '#d8b58b', fill: '#8d5b34', shadow: '#4b2d17', stroke: 'rgba(255,237,213,0.24)' };
    case 'water':
      return { accent: '#7dd3fc', fill: '#2563eb', shadow: '#0f2f75', stroke: 'rgba(186,230,253,0.28)' };
    case 'fire':
      return { accent: '#fdba74', fill: '#f97316', shadow: '#7c2d12', stroke: 'rgba(255,237,213,0.24)' };
    case 'poison':
      return { accent: '#bef264', fill: '#65a30d', shadow: '#365314', stroke: 'rgba(236,252,203,0.24)' };
    case 'ice':
      return { accent: '#e0f2fe', fill: '#7dd3fc', shadow: '#1d4ed8', stroke: 'rgba(224,242,254,0.28)' };
    case 'grass':
    default:
      return { accent: '#bbf7d0', fill: '#4ade80', shadow: '#166534', stroke: 'rgba(220,252,231,0.24)' };
  }
}

function resolveSurfaceTexture(code: SceneSurfaceDefinition['code']): HTMLImageElement | null {
  const surfaceDefinition = surfaceCatalog.value.find((item) => item.code === code);

  return resolveCachedImage(surfaceDefinition?.image_url ?? null);
}

function resolveCachedImage(url: string | null | undefined): HTMLImageElement | null {
  if (!url || brokenImageUrls.has(url)) {
    return null;
  }

  const cachedImage = imageCache.get(url);

  if (cachedImage && cachedImage.complete && cachedImage.naturalWidth > 0) {
    return cachedImage;
  }

  if (loadingImageUrls.has(url)) {
    return null;
  }

  const image = new Image();
  image.decoding = 'async';
  image.src = url;
  loadingImageUrls.add(url);
  image.onload = () => {
    loadingImageUrls.delete(url);
    imageCache.set(url, image);
    scheduleCanvasRender();
  };
  image.onerror = () => {
    loadingImageUrls.delete(url);
    brokenImageUrls.add(url);
    imageCache.delete(url);
    scheduleCanvasRender();
  };
  imageCache.set(url, image);

  return null;
}

function resolveWorldCoordinate(value: number, dimension: number): number {
  return (value - (dimension / 2)) * TILE_WORLD_SIZE;
}

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

  return {
    x: (canvasSize.value.width / 2) + viewport.value.offsetX + (rotatedX * viewport.value.zoom),
    y: (canvasSize.value.height / 2) + viewport.value.offsetY + (projectedY * viewport.value.zoom),
  };
}

function projectCell(cell: RuntimeSceneDetail['scene_template']['cells'][number]): ProjectedCell {
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

function getProjectedCells(): ProjectedCell[] {
  return (sceneTemplate.value?.cells ?? [])
    .map((cell) => projectCell(cell))
    .sort((left, right) => left.center.y - right.center.y);
}

function drawPolygon(context: CanvasRenderingContext2D, polygon: CanvasPoint[]): void {
  context.beginPath();
  polygon.forEach((point, index) => {
    if (index === 0) {
      context.moveTo(point.x, point.y);
      return;
    }

    context.lineTo(point.x, point.y);
  });
  context.closePath();
}

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

  drawPolygon(context, projectedCell.corners);
  context.fillStyle = gradient;
  context.fill();

  if (textureImage !== null) {
    context.save();
    drawPolygon(context, projectedCell.corners);
    context.clip();
    context.globalAlpha = 0.42;
    const topLeft = projectedCell.corners[0];
    const topRight = projectedCell.corners[1];
    const bottomLeft = projectedCell.corners[3];
    const basisX = { x: topRight.x - topLeft.x, y: topRight.y - topLeft.y };
    const basisY = { x: bottomLeft.x - topLeft.x, y: bottomLeft.y - topLeft.y };
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
  context.restore();
}

function drawBush(context: CanvasRenderingContext2D, projectedCell: ProjectedCell): void {
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

  const foliage = [
    { color: '#4ade80', radius: 26, x: 0, y: -42 },
    { color: '#3abf68', radius: 22, x: -22, y: -28 },
    { color: '#34a853', radius: 22, x: 20, y: -26 },
    { color: '#72e09b', radius: 20, x: 0, y: -16 },
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
  }

  context.restore();
}

function drawBarrel(context: CanvasRenderingContext2D, projectedCell: ProjectedCell): void {
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
  context.fillStyle = '#c48a53';
  context.beginPath();
  context.ellipse(0, -bodyHeight, bodyWidth / 2, 10 * scale, 0, 0, Math.PI * 2);
  context.fill();

  for (const offset of [-bodyHeight + (bodyHeight * 0.24), -bodyHeight + (bodyHeight * 0.52), -bodyHeight + (bodyHeight * 0.78)]) {
    context.strokeStyle = '#e5d2a9';
    context.lineWidth = 3.5 * scale;
    context.beginPath();
    context.moveTo(-(bodyWidth / 2) + (2 * scale), offset);
    context.lineTo((bodyWidth / 2) - (2 * scale), offset);
    context.stroke();
  }

  context.fillStyle = 'rgba(255, 249, 234, 0.24)';
  context.beginPath();
  context.ellipse(-(bodyWidth * 0.16), -bodyHeight + (bodyHeight * 0.3), bodyWidth * 0.12, bodyHeight * 0.3, 0, 0, Math.PI * 2);
  context.fill();
  context.restore();
}

function resolveSceneObjectImage(code: SceneObjectDefinition['code']): HTMLImageElement | null {
  const objectDefinition = objectCatalog.value.find((item) => item.code === code);

  return resolveCachedImage(objectDefinition?.image_url ?? null);
}

function drawSceneObject(context: CanvasRenderingContext2D, projectedCell: ProjectedCell, objectKind: SceneObjectDefinition['code']): void {
  const image = resolveSceneObjectImage(objectKind);

  if (image === null) {
    if (objectKind === 'bush') {
      drawBush(context, projectedCell);
    }

    if (objectKind === 'barrel') {
      drawBarrel(context, projectedCell);
    }

    return;
  }

  const baseX = projectedCell.center.x;
  const baseY = projectedCell.center.y - 6;
  const tileWidth = projectedCell.bounds.maxX - projectedCell.bounds.minX;
  const tileHeight = projectedCell.bounds.maxY - projectedCell.bounds.minY;
  const maxWidth = Math.max(46, tileWidth * 0.92);
  const maxHeight = Math.max(60, tileHeight * 1.55);
  const sourceRatio = image.width / image.height;
  let drawWidth = maxWidth;
  let drawHeight = drawWidth / sourceRatio;

  if (drawHeight > maxHeight) {
    drawHeight = maxHeight;
    drawWidth = drawHeight * sourceRatio;
  }

  context.save();
  context.fillStyle = 'rgba(15, 23, 42, 0.34)';
  context.beginPath();
  context.ellipse(baseX, baseY + 6, drawWidth * 0.32, 10, 0, 0, Math.PI * 2);
  context.fill();
  context.drawImage(image, baseX - (drawWidth / 2), baseY - drawHeight, drawWidth, drawHeight);
  context.restore();
}

function drawItemDrop(context: CanvasRenderingContext2D, itemDrop: RuntimeSceneDetail['item_drops'][number]): void {
  const cell = sceneTemplate.value?.cells.find((candidate) => candidate.x === itemDrop.x && candidate.y === itemDrop.y);
  const itemImage = resolveCachedImage(itemDrop.image_url ?? null);

  if (!cell) {
    return;
  }

  const projectedCell = projectCell(cell);
  const baseX = projectedCell.center.x;
  const baseY = projectedCell.center.y - 10;

  context.save();
  context.fillStyle = 'rgba(15, 23, 42, 0.28)';
  context.beginPath();
  context.ellipse(baseX, baseY + 12, 18, 10, 0, 0, Math.PI * 2);
  context.fill();

  if (itemImage !== null) {
    const iconWidth = 34;
    const iconHeight = 34;
    context.save();
    context.beginPath();
    context.roundRect(baseX - (iconWidth / 2), baseY - 24, iconWidth, iconHeight, 10);
    context.clip();
    context.drawImage(itemImage, baseX - (iconWidth / 2), baseY - 24, iconWidth, iconHeight);
    context.restore();

    context.lineWidth = 2;
    context.strokeStyle = 'rgba(254,243,199,0.85)';
    context.strokeRect(baseX - (iconWidth / 2), baseY - 24, iconWidth, iconHeight);
  } else {
    context.fillStyle = '#f59e0b';
    context.beginPath();
    context.moveTo(baseX, baseY - 18);
    context.lineTo(baseX + 16, baseY - 2);
    context.lineTo(baseX, baseY + 14);
    context.lineTo(baseX - 16, baseY - 2);
    context.closePath();
    context.fill();

    context.strokeStyle = '#fef3c7';
    context.lineWidth = 2;
    context.stroke();

    context.fillStyle = '#fff7ed';
    context.font = '700 10px Vollkorn, serif';
    context.textAlign = 'center';
    context.fillText(itemDrop.quantity > 1 ? `x${itemDrop.quantity}` : itemDrop.name.slice(0, 1).toUpperCase(), baseX, baseY + 3);
  }

  if (itemDrop.quantity > 1) {
    context.fillStyle = '#fff7ed';
    context.font = '700 10px Vollkorn, serif';
    context.textAlign = 'center';
    context.fillText(`x${itemDrop.quantity}`, baseX, baseY + 24);
  }
  context.restore();
}

function resolveActorImage(actor: RuntimeActorInstance): HTMLImageElement | null {
  return resolveCachedImage(actor.image_url ?? actor.runtime_state?.image_url ?? null);
}

function resolveActorCardSize(): { height: number; width: number } {
  const width = Math.max(56, Math.round((TILE_WORLD_SIZE * viewport.value.zoom * 0.58) - 10));

  return {
    width,
    height: Math.round(width * (4 / 3)),
  };
}

function resolveAnimatedActorPosition(actor: RuntimeActorInstance): { x: number; y: number } {
  const movementAnimation = movementAnimations.value[actor.id];

  if (movementAnimation === undefined || actor.x === null || actor.y === null) {
    return {
      x: actor.x ?? 0,
      y: actor.y ?? 0,
    };
  }

  return {
    x: movementAnimation.fromX + ((movementAnimation.toX - movementAnimation.fromX) * movementAnimation.progress),
    y: movementAnimation.fromY + ((movementAnimation.toY - movementAnimation.fromY) * movementAnimation.progress),
  };
}

function projectActorPosition(actor: RuntimeActorInstance): CanvasPoint {
  const animatedPosition = resolveAnimatedActorPosition(actor);
  const cell = sceneTemplate.value?.cells.find((item) => item.x === Math.round(animatedPosition.x) && item.y === Math.round(animatedPosition.y));
  const elevation = (cell?.elevation ?? 0) * ELEVATION_STEP;
  const worldX = resolveWorldCoordinate(animatedPosition.x, gridWidth.value) + (TILE_WORLD_SIZE / 2);
  const worldY = resolveWorldCoordinate(animatedPosition.y, gridHeight.value) + (TILE_WORLD_SIZE / 2);

  return projectWorldPoint(worldX, worldY, elevation);
}

function roundRectPath(context: CanvasRenderingContext2D, x: number, y: number, width: number, height: number, radius: number): void {
  context.beginPath();
  context.moveTo(x + radius, y);
  context.lineTo(x + width - radius, y);
  context.quadraticCurveTo(x + width, y, x + width, y + radius);
  context.lineTo(x + width, y + height - radius);
  context.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
  context.lineTo(x + radius, y + height);
  context.quadraticCurveTo(x, y + height, x, y + height - radius);
  context.lineTo(x, y + radius);
  context.quadraticCurveTo(x, y, x + radius, y);
  context.closePath();
}

function drawActor(context: CanvasRenderingContext2D, actor: RuntimeActorInstance, isSelected = false): void {
  const isPlayerHero = actor.kind === 'player_character';
  const basePoint = projectActorPosition(actor);
  const cardSize = resolveActorCardSize();
  const baseWidth = Math.max(42, cardSize.width - 2);
  const cardX = basePoint.x - (cardSize.width / 2);
  const cardY = basePoint.y - cardSize.height - 8;
  const radius = 12;
  context.save();
  context.fillStyle = 'rgba(15, 23, 42, 0.34)';
  context.beginPath();
  context.ellipse(basePoint.x, basePoint.y + 6, baseWidth * 0.42, 10, 0, 0, Math.PI * 2);
  context.fill();

  const pedestalGradient = context.createLinearGradient(basePoint.x, basePoint.y - 10, basePoint.x, basePoint.y + 10);
  pedestalGradient.addColorStop(0, isPlayerHero ? '#dbeafe' : '#efdbba');
  pedestalGradient.addColorStop(0.35, isPlayerHero ? '#60a5fa' : '#c69a62');
  pedestalGradient.addColorStop(1, isPlayerHero ? '#1e3a8a' : '#5d381d');
  context.fillStyle = pedestalGradient;
  context.beginPath();
  context.ellipse(basePoint.x, basePoint.y, baseWidth * 0.45, 8.5, 0, 0, Math.PI * 2);
  context.fill();
  context.fillStyle = isPlayerHero ? '#60a5fa' : '#b08a57';
  context.fillRect(basePoint.x - 2.2, basePoint.y - 22, 4.4, 24);
  context.fillStyle = isPlayerHero ? '#e0f2fe' : '#f3d8a6';
  context.fillRect(basePoint.x - 0.9, basePoint.y - 22, 1.8, 24);

  const frameFill = context.createLinearGradient(cardX, cardY, cardX + cardSize.width, cardY + cardSize.height);
  frameFill.addColorStop(0, isPlayerHero ? '#eff6ff' : '#f5e7c8');
  frameFill.addColorStop(0.3, isPlayerHero ? '#93c5fd' : '#c6975b');
  frameFill.addColorStop(0.7, isPlayerHero ? '#1d4ed8' : '#7e5734');
  frameFill.addColorStop(1, isPlayerHero ? '#dbeafe' : '#f3ddb2');
  context.fillStyle = frameFill;
  roundRectPath(context, cardX, cardY, cardSize.width, cardSize.height, radius);
  context.fill();

  const inset = 4;
  context.save();
  roundRectPath(context, cardX + inset, cardY + inset, cardSize.width - (inset * 2), cardSize.height - (inset * 2), radius - 2);
  context.clip();

  const image = resolveActorImage(actor);

  if (image !== null) {
    const targetWidth = cardSize.width - (inset * 2);
    const targetHeight = cardSize.height - (inset * 2);
    const sourceRatio = image.width / image.height;
    const targetRatio = targetWidth / targetHeight;
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
      targetWidth,
      targetHeight,
    );
  } else {
    const placeholderGradient = context.createLinearGradient(cardX, cardY, cardX, cardY + cardSize.height);
    placeholderGradient.addColorStop(0, '#5b446f');
    placeholderGradient.addColorStop(1, '#160f23');
    context.fillStyle = placeholderGradient;
    context.fillRect(cardX + inset, cardY + inset, cardSize.width - (inset * 2), cardSize.height - (inset * 2));
    context.fillStyle = '#fef3c7';
    context.font = '700 28px Vollkorn, serif';
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.fillText(actor.name.slice(0, 1).toUpperCase(), basePoint.x, cardY + (cardSize.height / 2));
  }

  const gloss = context.createLinearGradient(cardX, cardY, cardX, cardY + (cardSize.height * 0.45));
  gloss.addColorStop(0, 'rgba(255,255,255,0.28)');
  gloss.addColorStop(0.35, 'rgba(255,255,255,0.08)');
  gloss.addColorStop(1, 'rgba(255,255,255,0)');
  context.fillStyle = gloss;
  context.fillRect(cardX + inset, cardY + inset, cardSize.width - (inset * 2), cardSize.height * 0.42);
  context.restore();

  context.lineWidth = isSelected ? 3 : isPlayerHero ? 2.8 : actor.kind === 'npc' ? 2.7 : 2.1;
  context.strokeStyle = isSelected ? '#facc15' : isPlayerHero ? '#bfdbfe' : actor.kind === 'npc' ? '#f6d48b' : '#f8fafc';
  context.shadowBlur = isPlayerHero ? 26 : 18;
  context.shadowColor = isPlayerHero ? 'rgba(96, 165, 250, 0.42)' : 'rgba(15, 23, 42, 0.34)';
  roundRectPath(context, cardX, cardY, cardSize.width, cardSize.height, radius);
  context.stroke();
  if (isPlayerHero && !isSelected) {
    context.lineWidth = 1.2;
    context.strokeStyle = 'rgba(224, 242, 254, 0.85)';
    roundRectPath(context, cardX + 3, cardY + 3, cardSize.width - 6, cardSize.height - 6, radius - 2);
    context.stroke();
  }
  context.restore();
}

function drawSceneEntities(context: CanvasRenderingContext2D, projectedCells: ProjectedCell[]): void {
  const placements = projectedCells
    .map((projectedCell) => ({
      itemDrop: getItemDropAtCell(projectedCell.cell.x, projectedCell.cell.y),
      object: getObjectAtCell(projectedCell.cell.x, projectedCell.cell.y),
      projectedCell,
    }))
    .sort((left, right) => left.projectedCell.center.y - right.projectedCell.center.y);

  for (const placement of placements) {
    if (placement.object?.kind) {
      drawSceneObject(context, placement.projectedCell, placement.object.kind);
    }

    if (placement.itemDrop) {
      drawItemDrop(context, placement.itemDrop);
    }
  }

  const actors = [...(runtimeScene.value?.actor_instances ?? [])]
    .filter((actor) => actor.x !== null && actor.y !== null)
    .sort((left, right) => projectActorPosition(left).y - projectActorPosition(right).y);

  for (const actor of actors) {
    drawActor(context, actor, selectedActorId.value === actor.id);
  }
}

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
}

function scheduleCanvasRender(): void {
  if (renderFrameId.value !== null) {
    cancelAnimationFrame(renderFrameId.value);
  }

  renderFrameId.value = window.requestAnimationFrame(() => {
    renderFrameId.value = null;
    renderCanvasScene();
  });
}

function tickMovementAnimation(timestamp: number): void {
  const nextAnimations = Object.values(movementAnimations.value);

  if (nextAnimations.length === 0) {
    animationFrameId.value = null;
    return;
  }

  const updatedAnimations: Record<number, MovementAnimation> = {};

  nextAnimations.forEach((animation) => {
    const elapsed = timestamp - animation.startedAt;
    const progress = Math.min(1, elapsed / ANIMATION_DURATION);

    if (progress < 1) {
      updatedAnimations[animation.actorId] = {
        ...animation,
        progress,
      };
    }
  });

  movementAnimations.value = updatedAnimations;
  scheduleCanvasRender();

  if (Object.keys(updatedAnimations).length > 0) {
    animationFrameId.value = window.requestAnimationFrame(tickMovementAnimation);
    return;
  }

  animationFrameId.value = null;
  scheduleCanvasRender();
}

function startMovementAnimation(actorId: number, fromX: number, fromY: number, toX: number, toY: number): void {
  movementAnimations.value = {
    ...movementAnimations.value,
    [actorId]: {
      actorId,
      fromX,
      fromY,
      toX,
      toY,
      progress: 0,
      startedAt: performance.now(),
    },
  };

  if (animationFrameId.value !== null) {
    cancelAnimationFrame(animationFrameId.value);
  }

  animationFrameId.value = window.requestAnimationFrame(tickMovementAnimation);
}

function setupCanvasSize(): void {
  const canvas = canvasRef.value;
  const viewportElement = canvasViewportRef.value;

  if (canvas === null || viewportElement === null) {
    return;
  }

  const width = Math.max(680, Math.round(viewportElement.clientWidth));
  const height = Math.max(MIN_CANVAS_HEIGHT, Math.round(viewportElement.clientHeight));
  const dpr = window.devicePixelRatio || 1;

  canvas.width = Math.round(width * dpr);
  canvas.height = Math.round(height * dpr);
  canvas.style.width = `${width}px`;
  canvas.style.height = `${height}px`;
  canvasSize.value = { width, height };
  scheduleCanvasRender();
}

function setupCanvasResizeObserver(): void {
  if (canvasViewportRef.value === null) {
    return;
  }

  resizeObserver?.disconnect();
  resizeObserver = new ResizeObserver(() => {
    setupCanvasSize();
  });
  resizeObserver.observe(canvasViewportRef.value);
}

function resolveCanvasPoint(event: MouseEvent): CanvasPoint | null {
  const canvas = canvasRef.value;

  if (canvas === null) {
    return null;
  }

  const bounds = canvas.getBoundingClientRect();

  return {
    x: event.clientX - bounds.left,
    y: event.clientY - bounds.top,
  };
}

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

function findCellAtCanvasPoint(point: CanvasPoint): ProjectedCell | null {
  const projectedCells = getProjectedCells();

  for (let index = projectedCells.length - 1; index >= 0; index -= 1) {
    const projectedCell = projectedCells[index];

    if (
      point.x < projectedCell.bounds.minX
      || point.x > projectedCell.bounds.maxX
      || point.y < projectedCell.bounds.minY
      || point.y > projectedCell.bounds.maxY
    ) {
      continue;
    }

    if (isPointInsidePolygon(point, projectedCell.corners)) {
      return projectedCell;
    }
  }

  return null;
}

function handleCanvasMouseDown(event: MouseEvent): void {
  if (event.button !== 2) {
    closeActorContextMenu();
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
    pointerMode.value = 'pan';
    event.preventDefault();
  }

  if (event.button === 1) {
    pointerMode.value = 'rotate';
    event.preventDefault();
  }
}

function handleCanvasContextMenu(event: MouseEvent): void {
  const point = resolveCanvasPoint(event);
  const clickedCell = point ? findCellAtCanvasPoint(point) : null;

  if (clickedCell === null) {
    closeActorContextMenu();
    return;
  }

  const actorAtCell = getActorAtCell(clickedCell.cell.x, clickedCell.cell.y);

  if (!actorAtCell) {
    closeActorContextMenu();
    return;
  }

  selectedCellKey.value = resolveCellKey(clickedCell.cell.x, clickedCell.cell.y);
  selectedActorId.value = actorAtCell.id;
  actorContextMenu.value = {
    actorId: actorAtCell.id,
    x: event.clientX,
    y: event.clientY,
  };
  scheduleCanvasRender();
}

function handleGlobalMouseMove(event: MouseEvent): void {
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
      rotateZ: Math.min(135, Math.max(-45, pointerStartRotateZ.value + (deltaX * 0.35))),
    };
    scheduleCanvasRender();
    return;
  }

  const point = resolveCanvasPoint(event);
  const hoveredProjection = point ? findCellAtCanvasPoint(point) : null;
  hoveredCellKey.value = hoveredProjection ? resolveCellKey(hoveredProjection.cell.x, hoveredProjection.cell.y) : null;

  if (hoveredProjection !== null) {
    const actor = getActorAtCell(hoveredProjection.cell.x, hoveredProjection.cell.y);
    hoveredActorTooltip.value = actor
      ? {
          name: actor.name,
          x: point!.x + 16,
          y: point!.y + 16,
        }
      : null;
  } else {
    hoveredActorTooltip.value = null;
  }

  scheduleCanvasRender();
}

async function handleCellClick(x: number, y: number): Promise<void> {
  selectedCellKey.value = resolveCellKey(x, y);

  const actorAtCell = getActorAtCell(x, y);

  if (actorAtCell) {
    if (actorAtCell.controlled_by_user_id === currentUser.value?.id) {
      selectedActorId.value = actorAtCell.id;
    }

    scheduleCanvasRender();
    return;
  }

  if (selectedActor.value === null || selectedActor.value.x === null || selectedActor.value.y === null || gameId.value === null || isActorMoving.value) {
    scheduleCanvasRender();
    return;
  }

  try {
    isActorMoving.value = true;
    runtimeError.value = '';
    const movingActor = selectedActor.value;
    const movementDistance = resolveEncounterDistance(movingActor, x, y);

    if (!canActorMoveNow(movingActor)) {
      runtimeError.value = `Сейчас ходит ${currentEncounterActor.value?.name ?? 'другой участник'}.`;
      return;
    }

    if (activeEncounter.value !== null && movementDistance > (selectedEncounterParticipant.value?.movement_left ?? 0)) {
      runtimeError.value = 'Для такого перемещения не хватает оставшейся скорости.';
      return;
    }

    const previousPosition = {
      x: movingActor.x,
      y: movingActor.y,
    };
    const updatedActor = await movePlayerRuntimeActor(gameId.value, movingActor.id, { x, y });
    const actorIndex = runtimeScene.value?.actor_instances.findIndex((actor) => actor.id === movingActor.id) ?? -1;

    if (runtimeScene.value !== null && actorIndex >= 0) {
      runtimeScene.value.actor_instances.splice(actorIndex, 1, updatedActor);
      applyLocalEncounterMovement(movingActor.id, updatedActor, movementDistance);
      runtimeScene.value.version += 1;
      startMovementAnimation(movingActor.id, previousPosition.x, previousPosition.y, x, y);
      selectedActorId.value = updatedActor.id;
      selectedCellKey.value = resolveCellKey(x, y);
    }
  } catch (error) {
    runtimeError.value = (error as Error).message;
  } finally {
    isActorMoving.value = false;
    scheduleCanvasRender();
  }
}

async function handleCanvasMoveRequest(payload: { actor: RuntimeActorInstance; x: number; y: number }): Promise<void> {
  if (gameId.value === null || runtimeScene.value === null || isActorMoving.value) {
    return;
  }

  try {
    isActorMoving.value = true;
    runtimeError.value = '';
    const movingActor = payload.actor;
    const movementDistance = resolveEncounterDistance(movingActor, payload.x, payload.y);

    if (!canActorMoveNow(movingActor)) {
      runtimeError.value = `Сейчас ходит ${currentEncounterActor.value?.name ?? 'другой участник'}.`;
      return;
    }

    if (activeEncounter.value !== null && movementDistance > (selectedEncounterParticipant.value?.movement_left ?? 0)) {
      runtimeError.value = 'Для такого перемещения не хватает оставшейся скорости.';
      return;
    }

    const previousPosition = {
      x: movingActor.x,
      y: movingActor.y,
    };
    const updatedActor = await movePlayerRuntimeActor(gameId.value, movingActor.id, { x: payload.x, y: payload.y });
    const actorIndex = runtimeScene.value.actor_instances.findIndex((actor) => actor.id === movingActor.id);

    if (actorIndex >= 0) {
      runtimeScene.value.actor_instances.splice(actorIndex, 1, updatedActor);
      applyLocalEncounterMovement(movingActor.id, updatedActor, movementDistance);
      runtimeScene.value.version += 1;
      startMovementAnimation(movingActor.id, previousPosition.x, previousPosition.y, payload.x, payload.y);
      selectedActorId.value = updatedActor.id;
      selectedCellKey.value = resolveCellKey(payload.x, payload.y);
    }
  } catch (error) {
    runtimeError.value = (error as Error).message;
  } finally {
    isActorMoving.value = false;
    scheduleCanvasRender();
  }
}

function handleGlobalMouseUp(event: MouseEvent): void {
  const mode = pointerMode.value;
  pointerMode.value = null;

  if (event.button === 1) {
    event.preventDefault();
  }

  if (mode === null || hasMoved.value) {
    hasMoved.value = false;
    return;
  }

  const point = resolveCanvasPoint(event);
  const clickedCell = point ? findCellAtCanvasPoint(point) : null;

  if (clickedCell !== null) {
    void handleCellClick(clickedCell.cell.x, clickedCell.cell.y);
  } else {
    selectedCellKey.value = null;
    scheduleCanvasRender();
  }
}

function handleCanvasMouseLeave(): void {
  hoveredCellKey.value = null;
  hoveredActorTooltip.value = null;
  scheduleCanvasRender();
}

function handleCanvasWheel(event: WheelEvent): void {
  event.preventDefault();
  const nextZoom = viewport.value.zoom + (event.deltaY < 0 ? 0.08 : -0.08);
  viewport.value = {
    ...viewport.value,
    zoom: Math.min(1.8, Math.max(0.52, nextZoom)),
  };
  scheduleCanvasRender();
}

function applyRealtimeActorUpsert(actor: RuntimeActorInstance, animateMove: boolean): boolean {
  if (runtimeScene.value === null) {
    return false;
  }

  const actorIndex = runtimeScene.value.actor_instances.findIndex((item) => item.id === actor.id);
  const previousActor = actorIndex >= 0 ? runtimeScene.value.actor_instances[actorIndex] : null;

  if (previousActor && animateMove && previousActor.x !== null && previousActor.y !== null && actor.x !== null && actor.y !== null) {
    startMovementAnimation(actor.id, previousActor.x, previousActor.y, actor.x, actor.y);
  }

  if (actorIndex >= 0) {
    runtimeScene.value.actor_instances.splice(actorIndex, 1, actor);
  } else {
    runtimeScene.value.actor_instances.push(actor);
  }

  return true;
}

function applyRealtimeCellPaint(cell: NonNullable<RealtimeEventMessage['payload']['cell']>): boolean {
  const targetCell = runtimeScene.value?.scene_template.cells.find((item) => item.x === cell.x && item.y === cell.y);

  if (!targetCell) {
    return false;
  }

  targetCell.terrain_type = cell.terrain_type;
  targetCell.is_passable = cell.is_passable;
  targetCell.blocks_vision = cell.blocks_vision;

  return true;
}

function applyRealtimeItemDrop(itemDrop: NonNullable<RealtimeEventMessage['payload']['itemDrop']>): boolean {
  if (runtimeScene.value === null) {
    return false;
  }

  const exists = runtimeScene.value.item_drops.some((item) => item.id === itemDrop.id);

  if (!exists) {
    runtimeScene.value.item_drops.push(itemDrop);
  }

  return true;
}

async function consumeEncounterAction(actionType: 'action' | 'bonus-action'): Promise<void> {
  if (gameId.value === null || currentControlledEncounterParticipant.value?.actor === undefined) {
    return;
  }

  try {
    isEncounterUpdating.value = true;
    runtimeError.value = '';
    runtimeScene.value = actionType === 'action'
      ? await usePlayerRuntimeAction(gameId.value, currentControlledEncounterParticipant.value.actor_id)
      : await usePlayerRuntimeBonusAction(gameId.value, currentControlledEncounterParticipant.value.actor_id);
    syncEncounterSelection();
    scheduleCanvasRender();
  } catch (error) {
    runtimeError.value = (error as Error).message;
  } finally {
    isEncounterUpdating.value = false;
  }
}

async function handleEndEncounterTurn(): Promise<void> {
  if (gameId.value === null || currentControlledEncounterParticipant.value === null) {
    return;
  }

  try {
    isEncounterUpdating.value = true;
    runtimeError.value = '';
    runtimeScene.value = await endPlayerRuntimeTurn(gameId.value, currentControlledEncounterParticipant.value.actor_id);
    syncEncounterSelection();
    scheduleCanvasRender();
  } catch (error) {
    runtimeError.value = (error as Error).message;
  } finally {
    isEncounterUpdating.value = false;
  }
}

function wait(ms: number): Promise<void> {
  return new Promise((resolve) => {
    window.setTimeout(resolve, ms);
  });
}

async function loadRuntimeScene(): Promise<boolean> {
  if (gameId.value === null) {
    runtimeError.value = 'Игра не найдена.';
    return false;
  }

  isRuntimeLoading.value = true;
  runtimeError.value = '';

  try {
    const [scene, objects, surfaces, items] = await Promise.all([
      fetchPlayerActiveRuntimeScene(gameId.value),
      fetchSceneObjects(),
      fetchSceneSurfaces(),
      fetchItems(),
    ]);
    runtimeScene.value = scene;
    objectCatalog.value = objects;
    surfaceCatalog.value = surfaces;
    itemCatalog.value = items;
    syncEncounterSelection();

    await nextTick();
    setupCanvasSize();
    scheduleCanvasRender();
    return true;
  } catch (error) {
    runtimeError.value = (error as Error).message;
    return false;
  } finally {
    isRuntimeLoading.value = false;
  }
}

async function reconnectRuntimeScene(attempts = 4): Promise<void> {
  if (isReconnectPending.value) {
    return;
  }

  isReconnectPending.value = true;

  try {
    for (let attempt = 0; attempt < attempts; attempt += 1) {
      const isLoaded = await loadRuntimeScene();

      if (isLoaded) {
        return;
      }

      if (attempt < attempts - 1) {
        await wait(350);
      }
    }
  } finally {
    isReconnectPending.value = false;
  }
}

async function handleRealtimeEvent(message: RealtimeEventMessage): Promise<void> {
  if (gameId.value === null || message.payload.gameId !== gameId.value) {
    return;
  }

  if (message.event === 'game-scene.activated') {
    await loadRuntimeScene();
    pushToast('Сцена обновлена', 'Мастер запустил или перезапустил активную сцену.', 'info');
    return;
  }

  if (message.event === 'game-scene.updated') {
    await loadRuntimeScene();
    pushToast('Сцена обновлена', 'Состояние активной сцены обновилось.', 'info');
    return;
  }

  if (runtimeScene.value === null) {
    return;
  }

  const nextVersion = message.payload.version ?? 0;

  if (nextVersion <= runtimeScene.value.version) {
    return;
  }

  if (nextVersion !== runtimeScene.value.version + 1) {
    await loadRuntimeScene();
    return;
  }

  if (message.event === 'game-scene.actor-moved' || message.event === 'game-scene.actor-spawned') {
    const actor = message.payload.actor as RuntimeActorInstance | undefined;

    if (!actor) {
      await loadRuntimeScene();
      return;
    }

    if (!applyRealtimeActorUpsert(actor, message.event === 'game-scene.actor-moved')) {
      await loadRuntimeScene();
      return;
    }
    runtimeScene.value.version = nextVersion;
    scheduleCanvasRender();
    return;
  }

  if (message.event === 'game-scene.cell-painted' && message.payload.cell) {
    if (!applyRealtimeCellPaint(message.payload.cell)) {
      await loadRuntimeScene();
      return;
    }
    runtimeScene.value.version = nextVersion;
    scheduleCanvasRender();
    return;
  }

  if (message.event === 'game-scene.item-dropped' && message.payload.itemDrop) {
    if (!applyRealtimeItemDrop(message.payload.itemDrop)) {
      await loadRuntimeScene();
      return;
    }
    runtimeScene.value.version = nextVersion;
    scheduleCanvasRender();
    return;
  }

  await loadRuntimeScene();
}

watch(
  () => [runtimeScene.value?.version, viewport.value.offsetX, viewport.value.offsetY, viewport.value.rotateX, viewport.value.rotateZ, viewport.value.zoom, selectedActorId.value, selectedCellKey.value, hoveredCellKey.value],
  () => {
    scheduleCanvasRender();
  },
);

onMounted(async () => {
  isClientReady.value = true;
  await ensureSessionLoaded();

  if (!isAuthenticated.value) {
    await router.replace('/');
    return;
  }

  if (currentUser.value?.canAccessGm) {
    await router.replace('/cabinet/gm');
    return;
  }

  await reconnectRuntimeScene();
  connectRealtime();

  if (!isCanvasOnlyMode) {
    setupCanvasResizeObserver();
    window.addEventListener('mousemove', handleGlobalMouseMove);
    window.addEventListener('mouseup', handleGlobalMouseUp);
  }
});

const unsubscribeRealtime = subscribeRealtime((message) => {
  void handleRealtimeEvent(message);
});

onBeforeUnmount(() => {
  unsubscribeRealtime();

  if (!isCanvasOnlyMode) {
    resizeObserver?.disconnect();
    resizeObserver = null;
    window.removeEventListener('mousemove', handleGlobalMouseMove);
    window.removeEventListener('mouseup', handleGlobalMouseUp);
  }

  if (renderFrameId.value !== null) {
    cancelAnimationFrame(renderFrameId.value);
  }

  if (animationFrameId.value !== null) {
    cancelAnimationFrame(animationFrameId.value);
  }
});
</script>

<template>
  <main
    v-if="isClientReady && currentUser"
    class="fixed inset-0 overflow-hidden bg-[radial-gradient(circle_at_top,rgba(255,221,168,0.18),transparent_22%),linear-gradient(180deg,#120c19_0%,#1f1630_55%,#0b1220_100%)] text-slate-100"
  >
    <div class="pointer-events-none absolute inset-0 opacity-30 [background-image:radial-gradient(circle_at_1px_1px,rgba(255,229,184,0.14)_1px,transparent_0)] [background-size:30px_30px]" />

    <div class="fixed left-6 top-6 z-30 flex flex-wrap items-center gap-3">
      <button
        class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-slate-950/70 px-4 py-2 text-sm text-amber-50 backdrop-blur transition hover:border-amber-200/40 hover:bg-slate-950/85"
        type="button"
        @click="router.push('/cabinet/player')"
      >
        <ArrowLeft class="h-4 w-4" />
        Назад
      </button>

      <span
        v-if="!isCanvasOnlyMode && isPending"
        class="rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-2 text-xs uppercase text-amber-100"
      >
        Проверяем сессию
      </span>
    </div>

    <div
      v-if="runtimeError"
      class="fixed left-6 top-24 z-20 rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100 backdrop-blur"
      :class="isCanvasOnlyMode ? 'right-6' : 'right-[25.5rem]'"
    >
      <div class="flex flex-wrap items-center justify-between gap-3">
        <span>{{ runtimeError }}</span>
        <button
          class="inline-flex items-center gap-2 rounded-full border border-rose-200/20 bg-slate-950/40 px-4 py-2 text-xs uppercase tracking-[0.18em] text-rose-50 transition hover:border-rose-200/40"
          type="button"
          :disabled="isReconnectPending"
          @click="void reconnectRuntimeScene()"
        >
          {{ isReconnectPending ? 'Подключаем...' : 'Подключиться снова' }}
        </button>
      </div>
    </div>

    <div
      v-else-if="isRuntimeLoading"
      class="fixed left-6 top-24 z-20 rounded-[1.75rem] border border-amber-200/10 bg-white/5 px-5 py-8 text-sm text-slate-300 backdrop-blur"
      :class="isCanvasOnlyMode ? 'right-6' : 'right-[25.5rem]'"
    >
      Загружаем активную сцену...
    </div>

    <template v-else-if="runtimeScene && sceneTemplate">
      <RuntimeSceneCanvas
        v-if="isCanvasOnlyMode"
        v-model:selected-actor-id="selectedActorId"
        v-model:selected-cell-key="selectedCellKey"
        :current-user-id="currentUser.id"
        :object-catalog="objectCatalog"
        :runtime-scene="runtimeScene"
        :surface-catalog="surfaceCatalog"
        selection-mode="controlled"
        @move-actor="void handleCanvasMoveRequest($event)"
      />

      <div
        v-else
        class="scene-runtime-layout"
      >
        <section class="scene-runtime-shell">
          <div class="scene-runtime-hintbar">
            <span>{{ sceneName }}</span>
            <span>ЛКМ: выбор героя или ход</span>
            <span>ЛКМ drag: перемещение поля</span>
            <span>СКМ drag: поворот камеры</span>
            <span>Колесо: zoom</span>
          </div>

          <div
            v-if="activeEncounter"
            class="scene-runtime-initiative"
          >
            <div class="scene-runtime-initiative-head">
              <div>
                <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Сражение</p>
                <p class="mt-1 text-sm text-slate-300">Раунд {{ activeEncounter.round }} · Ходит {{ currentEncounterActor?.name ?? '—' }}</p>
              </div>
            </div>

            <div class="scene-runtime-initiative-track">
              <button
                v-for="participant in encounterParticipants"
                :key="participant.id"
                class="scene-runtime-initiative-card"
                :class="participant.id === activeEncounter.current_participant_id ? 'scene-runtime-initiative-card-active' : ''"
                type="button"
                @click="selectedActorId = participant.actor_id"
              >
                <img
                  v-if="participant.actor?.image_url"
                  :src="participant.actor.image_url"
                  :alt="participant.actor?.name ?? 'Участник'"
                  class="h-12 w-9 rounded-lg border border-white/10 object-cover object-top"
                >
                <div
                  v-else
                  class="flex h-12 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/10 text-xs font-semibold text-amber-100"
                >
                  {{ participant.actor?.name?.slice(0, 1) ?? '?' }}
                </div>
                <div class="min-w-0">
                  <p class="truncate text-sm text-amber-50">{{ participant.actor?.name ?? 'Участник' }}</p>
                  <p class="text-xs text-slate-400">Иниц. {{ participant.initiative ?? '—' }} · {{ participant.movement_left ?? 0 }} кл.</p>
                </div>
              </button>
            </div>
          </div>

          <div
            ref="canvasViewportRef"
            class="scene-runtime-viewport"
            @contextmenu.prevent="handleCanvasContextMenu"
            @mousedown="handleCanvasMouseDown"
            @mouseleave="handleCanvasMouseLeave"
            @wheel="handleCanvasWheel"
          >
            <canvas ref="canvasRef" class="scene-runtime-canvas" />

            <div
              v-if="hoveredActorTooltip"
              class="scene-hover-tooltip"
              :style="{ left: `${hoveredActorTooltip.x}px`, top: `${hoveredActorTooltip.y}px` }"
            >
              {{ hoveredActorTooltip.name }}
            </div>

            <div
              v-if="actorContextMenu"
              class="fixed z-40 min-w-48 rounded-[1.15rem] border border-amber-200/10 bg-slate-950/95 p-2 shadow-[0_18px_50px_rgba(2,6,23,0.55)]"
              :style="{ left: `${actorContextMenu.x}px`, top: `${actorContextMenu.y}px` }"
              @click.stop
              @mousedown.stop
              @contextmenu.prevent.stop
            >
              <button
                v-if="actorContextMenu && canOpenInventoryForActorId(actorContextMenu.actorId)"
                class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-amber-50 transition hover:bg-white/5"
                type="button"
                @click.stop="openInventoryForActor(actorContextMenu.actorId)"
                @mousedown.stop
              >
                Инвентарь
              </button>
              <button
                class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-slate-300 transition hover:bg-white/5"
                type="button"
                @click.stop="closeActorContextMenu()"
                @mousedown.stop
              >
                Закрыть
              </button>
            </div>
          </div>
        </section>

        <aside class="scene-runtime-panel">
          <div class="scene-runtime-panel-scroll">
            <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Сцена</p>
              <h1 class="mt-3 font-display text-3xl text-amber-50">{{ sceneName }}</h1>
              <p class="mt-3 text-sm leading-6 text-slate-300">
                {{ sceneTemplate.description || 'Описание runtime-сцены пока не заполнено.' }}
              </p>
              <div class="mt-4 flex flex-wrap gap-2 text-xs uppercase text-slate-400">
                <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-1">
                  {{ sceneTemplate.width }}x{{ sceneTemplate.height }}
                </span>
                <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-1">
                  Игра #{{ runtimeScene.game.id }}
                </span>
              </div>
            </section>

            <section
              v-if="activeEncounter"
              class="mt-4 rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4"
            >
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Боевой ход</p>
                  <p class="mt-1 text-sm text-slate-300">{{ currentEncounterActor?.name ?? '—' }}</p>
                </div>
                <span class="rounded-full border border-amber-200/10 bg-white/5 px-3 py-1 text-xs uppercase text-slate-300">
                  Раунд {{ activeEncounter.round }}
                </span>
              </div>

              <div class="mt-4 grid gap-2 text-sm text-slate-300">
                <div class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3">
                  Осталось перемещения: {{ currentControlledEncounterParticipant?.movement_left ?? '—' }} клеток
                </div>
                <div class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3">
                  Основное действие: {{ currentControlledEncounterParticipant?.action_available ? 'доступно' : 'израсходовано' }}
                </div>
                <div class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3">
                  Доп. действие: {{ currentControlledEncounterParticipant?.bonus_action_available ? 'доступно' : 'израсходовано' }}
                </div>
              </div>

              <div
                v-if="currentControlledEncounterParticipant"
                class="mt-4 grid gap-2"
              >
                <button
                  class="rounded-full border border-amber-200/20 bg-white/5 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/35 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="!currentControlledEncounterParticipant.action_available || isEncounterUpdating"
                  type="button"
                  @click="consumeEncounterAction('action')"
                >
                  Использовать основное действие
                </button>
                <button
                  class="rounded-full border border-amber-200/20 bg-white/5 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/35 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="!currentControlledEncounterParticipant.bonus_action_available || isEncounterUpdating"
                  type="button"
                  @click="consumeEncounterAction('bonus-action')"
                >
                  Использовать доп. действие
                </button>
                <button
                  class="rounded-full border border-amber-300/30 bg-amber-300/10 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/40 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="isEncounterUpdating"
                  type="button"
                  @click="handleEndEncounterTurn()"
                >
                  Завершить ход
                </button>
              </div>

              <p
                v-else
                class="mt-4 rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-400"
              >
                Сейчас ход другого участника. Ты сможешь действовать, когда очередь дойдет до твоего героя.
              </p>
            </section>

            <section class="mt-4 rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
              <div class="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-amber-200/50">
                <Shield class="h-4 w-4" />
                Твои герои
              </div>

              <div class="mt-4 space-y-2">
                <button
                  v-for="actor in controlledActors"
                  :key="actor.id"
                  class="flex w-full items-center justify-between gap-3 rounded-[1.15rem] border px-3 py-3 text-left transition"
                  :class="selectedActorId === actor.id ? 'border-amber-200/40 bg-amber-200/10 text-amber-50' : 'border-amber-200/10 bg-white/5 text-slate-200 hover:border-amber-200/20'"
                  type="button"
                  @click="selectedActorId = actor.id"
                >
                  <span class="truncate">{{ actor.name }}</span>
                  <span class="text-xs uppercase text-amber-200/60">
                    {{ actor.hp_current ?? '—' }}/{{ actor.hp_max ?? '—' }} HP
                  </span>
                </button>

                <p
                  v-if="controlledActors.length === 0"
                  class="rounded-[1.15rem] border border-amber-200/10 bg-white/5 px-3 py-3 text-sm text-slate-400"
                >
                  Твой герой пока не размещен на активной сцене.
                </p>
              </div>
            </section>

            <section
              v-if="selectedActor"
              class="mt-4 rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4"
            >
              <div class="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-amber-200/50">
                <Sword class="h-4 w-4" />
                Выбранный герой
              </div>
              <p class="mt-3 font-display text-2xl text-amber-50">{{ selectedActor.name }}</p>
              <p class="mt-2 text-sm text-slate-300">
                {{ selectedActor.runtime_state?.race || 'Без расы' }} · {{ selectedActor.runtime_state?.character_class || 'Без класса' }}
              </p>
              <p class="mt-2 text-sm text-slate-400">
                Скорость: {{ selectedActor.movement_speed ?? selectedActor.runtime_state?.movement_speed ?? '—' }} клеток
              </p>
              <p class="mt-4 text-sm leading-6 text-slate-300">
                Выбери героя, затем кликни по свободной клетке, чтобы переместить его. ПКМ по актеру открывает контекстное меню.
              </p>
              <p
                v-if="selectedEncounterParticipant"
                class="mt-3 rounded-2xl border border-amber-200/10 bg-slate-950/40 px-4 py-3 text-xs leading-5 text-slate-300"
              >
                В бою: осталось {{ selectedEncounterParticipant.movement_left ?? 0 }} клеток ·
                основное {{ selectedEncounterParticipant.action_available ? 'готово' : 'потрачено' }} ·
                доп. {{ selectedEncounterParticipant.bonus_action_available ? 'готово' : 'потрачено' }}
              </p>
            </section>
          </div>
        </aside>
      </div>
    </template>
  </main>

  <RuntimeActorInventoryModal
    v-if="isClientReady"
    :actor-name="inventoryActor?.name ?? ''"
    :catalog="itemCatalog"
    :items="normalizeInventory(inventoryActor?.runtime_state?.inventory)"
    :open="inventoryActor !== null"
    @close="inventoryActorId = null"
  />
</template>

<style scoped>
.scene-runtime-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 24rem;
  height: 100vh;
  width: 100vw;
  gap: 1.5rem;
  padding: 1.5rem;
}

.scene-runtime-shell {
  position: relative;
  min-width: 0;
  min-height: 0;
  padding-top: 12.25rem;
}

.scene-runtime-hintbar {
  position: absolute;
  top: 10rem;
  left: 0;
  z-index: 15;
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
  pointer-events: none;
}

.scene-runtime-hintbar span {
  border: 1px solid rgba(251, 191, 36, 0.12);
  background: rgba(2, 6, 23, 0.6);
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  font-size: 0.72rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: rgba(251, 191, 36, 0.76);
  backdrop-filter: blur(12px);
}

.scene-runtime-initiative {
  position: absolute;
  top: 4.75rem;
  left: 0;
  right: 0;
  z-index: 16;
  display: grid;
  gap: 0.75rem;
}

.scene-runtime-initiative-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.scene-runtime-initiative-track {
  display: flex;
  gap: 0.75rem;
  overflow-x: auto;
  padding-bottom: 0.25rem;
}

.scene-runtime-initiative-card {
  display: flex;
  min-width: 15rem;
  align-items: center;
  gap: 0.75rem;
  border-radius: 1.35rem;
  border: 1px solid rgba(251, 191, 36, 0.12);
  background: rgba(2, 6, 23, 0.72);
  padding: 0.75rem;
  text-align: left;
  backdrop-filter: blur(12px);
}

.scene-runtime-initiative-card-active {
  border-color: rgba(251, 191, 36, 0.34);
  background: rgba(120, 53, 15, 0.28);
  box-shadow: 0 12px 32px rgba(120, 53, 15, 0.18);
}

.scene-runtime-viewport {
  position: relative;
  height: 100%;
  min-height: 0;
  overflow: hidden;
  border-radius: 2rem;
  border: 1px solid rgba(251, 191, 36, 0.12);
  background:
    radial-gradient(circle at top, rgba(248, 216, 155, 0.08), transparent 32%),
    linear-gradient(180deg, rgba(17, 24, 39, 0.94), rgba(3, 7, 18, 0.98));
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.05),
    0 24px 80px rgba(0, 0, 0, 0.38);
  cursor: crosshair;
}

.scene-runtime-canvas {
  display: block;
  width: 100%;
  height: 100%;
  cursor: crosshair;
}

.scene-runtime-panel {
  position: relative;
  height: calc(100vh - 3rem);
  margin-top: 0;
  width: 24rem;
  border-radius: 2rem;
  border: 1px solid rgba(251, 191, 36, 0.12);
  background:
    linear-gradient(180deg, rgba(17, 24, 39, 0.92), rgba(2, 6, 23, 0.96)),
    radial-gradient(circle at top, rgba(251, 191, 36, 0.08), transparent 40%);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.05),
    0 24px 80px rgba(0, 0, 0, 0.38);
  backdrop-filter: blur(14px);
}

.scene-runtime-panel-scroll {
  height: 100%;
  overflow-y: auto;
  padding: 1.5rem;
}

.scene-hover-tooltip {
  position: absolute;
  z-index: 20;
  pointer-events: none;
  border-radius: 999px;
  border: 1px solid rgba(251, 191, 36, 0.2);
  background: rgba(2, 6, 23, 0.82);
  padding: 0.4rem 0.75rem;
  font-size: 0.72rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: rgba(251, 191, 36, 0.8);
  backdrop-filter: blur(8px);
}

@media (max-width: 1100px) {
  .scene-runtime-layout {
    grid-template-columns: minmax(0, 1fr);
    padding-right: 1rem;
  }

  .scene-runtime-panel {
    display: none;
  }
}
</style>

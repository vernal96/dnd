<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { SceneCell, SceneObjectDefinition, SceneSurfaceDefinition, SceneViewportMetadata } from '@/types/scene';
import type { RuntimeActorInstance, RuntimeSceneDetail } from '@/types/runtimeScene';

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
  object: RuntimeSceneDetail['scene_template']['objects'][number] & { x: number; y: number };
};

type PointerMode = 'pan' | 'rotate' | null;

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
const CANVAS_BACKGROUND = '#0a1120';
const ANIMATION_DURATION = 360;
const DEFAULT_VIEWPORT_ROTATE_X = 52;
const DEFAULT_VIEWPORT_ROTATE_Z = 45;
const MIN_VIEWPORT_ROTATE_X = 40;
const MAX_VIEWPORT_ROTATE_X = 68;
const MIN_VIEWPORT_ROTATE_Z = 12;
const MAX_VIEWPORT_ROTATE_Z = 78;

const props = withDefaults(defineProps<{
  currentUserId?: number | null;
  objectCatalog: SceneObjectDefinition[];
  runtimeScene: RuntimeSceneDetail;
  selectedActorId: number | null;
  selectedCellKey: string | null;
  selectionMode?: 'all' | 'controlled';
  surfaceCatalog: SceneSurfaceDefinition[];
}>(), {
  currentUserId: null,
  selectionMode: 'all',
});

const emit = defineEmits<{
  (event: 'move-actor', payload: { actor: RuntimeActorInstance; x: number; y: number }): void;
  (event: 'update:selectedActorId', value: number | null): void;
  (event: 'update:selectedCellKey', value: string | null): void;
}>();

const canvasViewportRef = ref<HTMLDivElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const hoveredCellKey = ref<string | null>(null);
const canvasSize = ref({
  height: 0,
  width: 0,
});
const viewport = ref<SceneViewportMetadata>({
  offsetX: 0,
  offsetY: 0,
  rotateX: DEFAULT_VIEWPORT_ROTATE_X,
  rotateZ: DEFAULT_VIEWPORT_ROTATE_Z,
  zoom: 1,
});

const pointerMode = ref<PointerMode>(null);
const pointerStartX = ref(0);
const pointerStartY = ref(0);
const pointerStartOffsetX = ref(0);
const pointerStartOffsetY = ref(0);
const pointerStartRotateX = ref(DEFAULT_VIEWPORT_ROTATE_X);
const pointerStartRotateZ = ref(DEFAULT_VIEWPORT_ROTATE_Z);
const hasMoved = ref(false);

const renderFrameId = ref<number | null>(null);
const animationFrameId = ref<number | null>(null);
const movementAnimations = ref<Record<number, MovementAnimation>>({});

const imageCache = new Map<string, HTMLImageElement>();
const brokenImageUrls = new Set<string>();
const loadingImageUrls = new Set<string>();
const previousActorPositions = new Map<number, { x: number | null; y: number | null }>();
let resizeObserver: ResizeObserver | null = null;
let initializedSceneId: number | null = null;

const sceneTemplate = computed(() => props.runtimeScene.scene_template);
const gridWidth = computed(() => sceneTemplate.value.width);
const gridHeight = computed(() => sceneTemplate.value.height);
const selectedActor = computed<RuntimeActorInstance | null>(() => {
  if (props.selectedActorId === null) {
    return null;
  }

  return props.runtimeScene.actor_instances.find((actor) => actor.id === props.selectedActorId) ?? null;
});
const activeEncounter = computed(() => props.runtimeScene.encounter);
const currentEncounterActorId = computed<number | null>(() => activeEncounter.value?.participants.find((participant) => participant.id === activeEncounter.value?.current_participant_id)?.actor_id ?? null);

function resolveCellKey(x: number, y: number): string {
  return `${x}-${y}`;
}

function initializeViewport(): void {
  if (initializedSceneId === props.runtimeScene.id) {
    return;
  }

  initializedSceneId = props.runtimeScene.id;
  viewport.value = {
    offsetX: 0,
    offsetY: 0,
    rotateX: DEFAULT_VIEWPORT_ROTATE_X,
    rotateZ: DEFAULT_VIEWPORT_ROTATE_Z,
    zoom: 1,
    ...(sceneTemplate.value.metadata?.viewport ?? {}),
    rotateX: DEFAULT_VIEWPORT_ROTATE_X,
    rotateZ: DEFAULT_VIEWPORT_ROTATE_Z,
  };
}

function canSelectActor(actor: RuntimeActorInstance): boolean {
  if (props.selectionMode === 'all') {
    return true;
  }

  return actor.controlled_by_user_id === props.currentUserId;
}

function canMoveActor(actor: RuntimeActorInstance): boolean {
  if (activeEncounter.value === null) {
    return true;
  }

  return currentEncounterActorId.value === actor.id;
}

function getObjectAtCell(x: number, y: number): RuntimeSceneDetail['scene_template']['objects'][number] | undefined {
  return sceneTemplate.value.objects.find((object) => objectOccupiesCell(object, x, y));
}

function objectOccupiesCell(object: RuntimeSceneDetail['scene_template']['objects'][number], x: number, y: number): boolean {
  if (object.x === null || object.y === null) {
    return false;
  }

  return x >= object.x
    && x < object.x + Math.max(1, object.width)
    && y >= object.y
    && y < object.y + Math.max(1, object.height);
}

function getActorAtCell(x: number, y: number): RuntimeActorInstance | undefined {
  return props.runtimeScene.actor_instances.find((actor) => actor.x === x && actor.y === y);
}

function getItemDropAtCell(x: number, y: number): RuntimeSceneDetail['item_drops'][number] | undefined {
  return props.runtimeScene.item_drops.find((item) => item.x === x && item.y === y);
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

function projectObjectFootprint(
  object: RuntimeSceneDetail['scene_template']['objects'][number] & { x: number; y: number },
): ProjectedObjectFootprint {
  const x0 = resolveWorldCoordinate(object.x, gridWidth.value);
  const y0 = resolveWorldCoordinate(object.y, gridHeight.value);
  const x1 = x0 + (TILE_WORLD_SIZE * Math.max(1, object.width));
  const y1 = y0 + (TILE_WORLD_SIZE * Math.max(1, object.height));
  const anchorCell = sceneTemplate.value.cells.find((cell) => cell.x === object.x && cell.y === object.y);
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

function getProjectedCells(): ProjectedCell[] {
  return sceneTemplate.value.cells
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

function resolveSurfaceTexture(code: SceneSurfaceDefinition['code']): HTMLImageElement | null {
  const surfaceDefinition = props.surfaceCatalog.find((item) => item.code === code);

  return resolveCachedImage(surfaceDefinition?.image_url ?? null);
}

function drawCellTextureOverlay(
  context: CanvasRenderingContext2D,
  projectedCell: ProjectedCell,
  textureImage: HTMLImageElement,
): void {
  const pattern = context.createPattern(textureImage, 'no-repeat');

  if (pattern === null) {
    return;
  }

  const origin = projectedCell.corners[0];
  const horizontalVector = {
    x: projectedCell.corners[1].x - origin.x,
    y: projectedCell.corners[1].y - origin.y,
  };
  const verticalVector = {
    x: projectedCell.corners[3].x - origin.x,
    y: projectedCell.corners[3].y - origin.y,
  };
  const safeImageWidth = Math.max(1, textureImage.naturalWidth || textureImage.width);
  const safeImageHeight = Math.max(1, textureImage.naturalHeight || textureImage.height);

  pattern.setTransform(new DOMMatrix([
    horizontalVector.x / safeImageWidth,
    horizontalVector.y / safeImageWidth,
    verticalVector.x / safeImageHeight,
    verticalVector.y / safeImageHeight,
    origin.x,
    origin.y,
  ]));

  context.save();
  drawPolygon(context, projectedCell.corners);
  context.clip();
  context.globalAlpha = 0.42;
  context.fillStyle = pattern;
  context.fillRect(
    projectedCell.bounds.minX,
    projectedCell.bounds.minY,
    projectedCell.bounds.maxX - projectedCell.bounds.minX,
    projectedCell.bounds.maxY - projectedCell.bounds.minY,
  );
  context.restore();
}

function drawCell(context: CanvasRenderingContext2D, projectedCell: ProjectedCell): void {
  const palette = resolveSurfacePalette(projectedCell.cell.terrain_type);
  const isSelected = props.selectedCellKey === resolveCellKey(projectedCell.cell.x, projectedCell.cell.y);
  const isHovered = hoveredCellKey.value === resolveCellKey(projectedCell.cell.x, projectedCell.cell.y);
  const textureImage = resolveSurfaceTexture(projectedCell.cell.terrain_type as SceneSurfaceDefinition['code']);
  const gradient = context.createLinearGradient(
    projectedCell.bounds.minX,
    projectedCell.bounds.minY,
    projectedCell.bounds.maxX,
    projectedCell.bounds.maxY,
  );

  gradient.addColorStop(0, palette.accent);
  gradient.addColorStop(0.45, palette.fill);
  gradient.addColorStop(1, palette.shadow);

  context.save();
  context.shadowColor = 'rgba(15, 23, 42, 0.35)';
  context.shadowBlur = 18;
  context.shadowOffsetY = 12;

  if (isSelected || isHovered) {
    drawPolygon(context, projectedCell.corners);
    context.fillStyle = isSelected ? 'rgba(251, 191, 36, 0.22)' : 'rgba(255, 255, 255, 0.08)';
    context.fill();
  }

  drawPolygon(context, projectedCell.corners);
  context.fillStyle = gradient;
  context.fill();

  if (textureImage !== null) {
    drawCellTextureOverlay(context, projectedCell, textureImage);
  }

  drawPolygon(context, projectedCell.corners);
  context.strokeStyle = palette.stroke;
  context.lineWidth = isSelected ? 3 : 1.4;
  context.stroke();
  context.restore();
}

function drawStylizedObject(
  context: CanvasRenderingContext2D,
  footprint: ProjectedObjectFootprint,
  objectDefinition: SceneObjectDefinition,
  objectInstance: RuntimeSceneDetail['scene_template']['objects'][number],
): void {
  const footprintWidth = footprint.bounds.maxX - footprint.bounds.minX;
  const footprintHeight = footprint.bounds.maxY - footprint.bounds.minY;
  const objectWidthInCells = Math.max(1, objectInstance.width || objectDefinition.width || 1);
  const objectHeightInCells = Math.max(1, objectInstance.height || objectDefinition.height || 1);
  const isMultiCellObject = objectWidthInCells > 1 || objectHeightInCells > 1;
  const baseScale = Math.max(0.72, viewport.value.zoom);
  const billboardWidth = Math.max(40, objectWidthInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.72);
  const billboardHeight = Math.max(44, objectHeightInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.72);
  const baseX = footprint.center.x;
  const baseY = isMultiCellObject
    ? footprint.center.y + Math.max(4, footprintHeight * 0.06)
    : footprint.bounds.maxY - Math.max(10, footprintHeight * 0.24);

  context.save();
  context.translate(baseX, baseY);

  if (objectDefinition.code === 'barrel') {
    const bodyWidth = Math.max(28, billboardWidth * 0.3);
    const bodyHeight = Math.max(36, billboardHeight * 0.44);
    context.fillStyle = '#6b3f22';
    context.beginPath();
    context.ellipse(0, 0, bodyWidth / 2, Math.max(8, bodyHeight * 0.22), 0, 0, Math.PI * 2);
    context.fill();
    context.fillRect(-(bodyWidth / 2), -bodyHeight, bodyWidth, bodyHeight);
    context.beginPath();
    context.ellipse(0, -bodyHeight, bodyWidth / 2, Math.max(8, bodyHeight * 0.22), 0, 0, Math.PI * 2);
    context.fill();

    for (const offset of [-bodyHeight + (bodyHeight * 0.24), -bodyHeight + (bodyHeight * 0.52), -bodyHeight + (bodyHeight * 0.78)]) {
      context.strokeStyle = '#e5d2a9';
      context.lineWidth = Math.max(2, baseScale * 3);
      context.beginPath();
      context.moveTo(-(bodyWidth / 2) + Math.max(2, baseScale * 2), offset);
      context.lineTo((bodyWidth / 2) - Math.max(2, baseScale * 2), offset);
      context.stroke();
    }

    context.fillStyle = 'rgba(255, 249, 234, 0.24)';
    context.beginPath();
    context.ellipse(-(bodyWidth * 0.16), -bodyHeight + (bodyHeight * 0.3), bodyWidth * 0.12, bodyHeight * 0.3, 0, 0, Math.PI * 2);
    context.fill();
  } else {
    if (objectDefinition.code === 'house') {
      const bodyWidth = Math.max(110, billboardWidth * 0.86);
      const bodyHeight = Math.max(84, billboardHeight * 0.52);
      const roofHeight = Math.max(58, bodyHeight * 0.68);
      const doorWidth = bodyWidth * 0.18;
      const doorHeight = bodyHeight * 0.42;
      const windowWidth = bodyWidth * 0.16;
      const windowHeight = bodyHeight * 0.22;
      context.fillStyle = '#7c4a2d';
      context.fillRect(-(bodyWidth / 2), -bodyHeight, bodyWidth, bodyHeight);
      context.fillStyle = '#c2410c';
      context.beginPath();
      context.moveTo(-(bodyWidth * 0.6), -(bodyHeight * 0.96));
      context.lineTo(0, -(bodyHeight + roofHeight));
      context.lineTo(bodyWidth * 0.6, -(bodyHeight * 0.96));
      context.closePath();
      context.fill();
      context.fillStyle = '#f5d0a9';
      context.fillRect(-(doorWidth / 2), 0 - doorHeight, doorWidth, doorHeight);
      context.fillRect(bodyWidth * 0.16, -(bodyHeight * 0.68), windowWidth, windowHeight);
      context.fillRect(-(bodyWidth * 0.32), -(bodyHeight * 0.68), windowWidth, windowHeight);
      context.restore();
      return;
    }

    const radiusX = Math.max(20, billboardWidth * 0.42);
    const radiusY = Math.max(16, billboardHeight * 0.2);
    context.fillStyle = '#1f5f3d';
    context.beginPath();
    context.ellipse(0, -(billboardHeight * 0.18), radiusX, radiusY, 0, 0, Math.PI * 2);
    context.fill();
    context.fillStyle = '#2f855a';
    context.beginPath();
    context.ellipse(-(billboardWidth * 0.08), -(billboardHeight * 0.08), radiusX * 0.85, radiusY * 0.8, 0, 0, Math.PI * 2);
    context.fill();
    context.beginPath();
    context.ellipse(billboardWidth * 0.1, -(billboardHeight * 0.04), radiusX * 0.72, radiusY * 0.7, 0, 0, Math.PI * 2);
    context.fill();
  }

  context.restore();
}

function drawObject(
  context: CanvasRenderingContext2D,
  footprint: ProjectedObjectFootprint,
  objectInstance: RuntimeSceneDetail['scene_template']['objects'][number] & { x: number; y: number },
): void {
  const objectDefinition = props.objectCatalog.find((item) => item.code === objectInstance.kind);

  if (!objectDefinition) {
    return;
  }

  const objectImage = resolveCachedImage(objectDefinition.image_url ?? null);

  if (objectImage !== null) {
    const objectWidthInCells = Math.max(1, objectInstance.width || objectDefinition.width || 1);
    const objectHeightInCells = Math.max(1, objectInstance.height || objectDefinition.height || 1);
    const isMultiCellObject = objectWidthInCells > 1 || objectHeightInCells > 1;
    const width = Math.max(48, objectWidthInCells * TILE_WORLD_SIZE * viewport.value.zoom * 0.82);
    const maxHeight = Math.max(48, objectHeightInCells * TILE_WORLD_SIZE * viewport.value.zoom * 1.08);
    let height = Math.max(48, width / Math.max(0.1, objectImage.width / objectImage.height));

    if (height > maxHeight) {
      height = maxHeight;
    }

    const normalizedWidth = height * Math.max(0.1, objectImage.width / objectImage.height);
    const y = isMultiCellObject
      ? footprint.center.y - (height / 2)
      : footprint.bounds.maxY - height - Math.max(10, (objectHeightInCells * 8) * viewport.value.zoom);
    context.drawImage(objectImage, footprint.center.x - (normalizedWidth / 2), y, normalizedWidth, height);
    return;
  }

  drawStylizedObject(context, footprint, objectDefinition, objectInstance);
}

function drawItemDrop(context: CanvasRenderingContext2D, itemDrop: RuntimeSceneDetail['item_drops'][number]): void {
  const cell = sceneTemplate.value.cells.find((candidate) => candidate.x === itemDrop.x && candidate.y === itemDrop.y);
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
  context.ellipse(baseX, baseY + 18, 22, 10, 0, 0, Math.PI * 2);
  context.fill();

  if (itemImage !== null) {
    context.drawImage(itemImage, baseX - 16, baseY - 18, 32, 32);
  } else {
    context.fillStyle = '#f8d8a0';
    context.beginPath();
    context.arc(baseX, baseY, 10, 0, Math.PI * 2);
    context.fill();
  }

  context.restore();
}

function resolveActorPortrait(actor: RuntimeActorInstance): HTMLImageElement | null {
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

  if (!movementAnimation) {
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
  const worldX = resolveWorldCoordinate(animatedPosition.x, gridWidth.value) + (TILE_WORLD_SIZE / 2);
  const worldY = resolveWorldCoordinate(animatedPosition.y, gridHeight.value) + (TILE_WORLD_SIZE / 2);
  const elevation = ((sceneTemplate.value.cells.find((cell) => cell.x === actor.x && cell.y === actor.y)?.elevation ?? 0) * ELEVATION_STEP) + 4;

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
  const portrait = resolveActorPortrait(actor);
  const cardX = basePoint.x - (cardSize.width / 2);
  const cardY = basePoint.y - cardSize.height - 8;
  const radius = 12;

  context.save();
  context.fillStyle = 'rgba(15, 23, 42, 0.34)';
  context.beginPath();
  context.ellipse(basePoint.x, basePoint.y + 14, 24, 9, 0, 0, Math.PI * 2);
  context.fill();

  roundRectPath(context, cardX, cardY, cardSize.width, cardSize.height, radius);
  const frameGradient = context.createLinearGradient(cardX, cardY, cardX, cardY + cardSize.height);
  frameGradient.addColorStop(0, isPlayerHero ? '#fbbf24' : '#e2e8f0');
  frameGradient.addColorStop(1, isPlayerHero ? '#92400e' : '#334155');
  context.fillStyle = frameGradient;
  context.fill();

  roundRectPath(context, cardX + 3, cardY + 3, cardSize.width - 6, cardSize.height - 6, radius - 2);
  context.fillStyle = '#0f172a';
  context.fill();

  if (portrait !== null) {
    context.save();
    roundRectPath(context, cardX + 6, cardY + 6, cardSize.width - 12, cardSize.height - 18, radius - 5);
    context.clip();
    context.drawImage(portrait, cardX + 6, cardY + 6, cardSize.width - 12, cardSize.height - 18);
    context.restore();
  } else {
    context.fillStyle = isPlayerHero ? '#92400e' : '#334155';
    roundRectPath(context, cardX + 6, cardY + 6, cardSize.width - 12, cardSize.height - 18, radius - 5);
    context.fill();
    context.fillStyle = '#f8fafc';
    context.font = `700 ${Math.max(18, cardSize.width * 0.32)}px Vollkorn, serif`;
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.fillText(actor.name.slice(0, 1), basePoint.x, cardY + ((cardSize.height - 10) / 2));
  }

  if (isPlayerHero) {
    context.fillStyle = 'rgba(251, 191, 36, 0.88)';
    context.fillRect(cardX + 6, cardY + cardSize.height - 14, cardSize.width - 12, 3);
  }

  if (isSelected) {
    context.strokeStyle = '#fde68a';
    context.lineWidth = 3;
    roundRectPath(context, cardX - 2, cardY - 2, cardSize.width + 4, cardSize.height + 4, radius + 2);
    context.stroke();
  }

  context.restore();
}

function drawSceneEntities(context: CanvasRenderingContext2D, projectedCells: ProjectedCell[]): void {
  const objectPlacements = sceneTemplate.value.objects
    .filter((object): object is RuntimeSceneDetail['scene_template']['objects'][number] & { x: number; y: number } => object.x !== null && object.y !== null)
    .map((object) => ({
      object,
      footprint: projectObjectFootprint(object),
    }))
    .sort((left, right) => left.footprint.bounds.maxY - right.footprint.bounds.maxY);

  objectPlacements.forEach(({ object, footprint }) => {
    drawObject(context, footprint, object);
  });

  projectedCells.forEach((projectedCell) => {
    const itemDrop = getItemDropAtCell(projectedCell.cell.x, projectedCell.cell.y);

    if (itemDrop) {
      drawItemDrop(context, itemDrop);
    }
  });

  projectedCells.forEach((projectedCell) => {
    const actor = getActorAtCell(projectedCell.cell.x, projectedCell.cell.y);

    if (actor) {
      drawActor(context, actor, actor.id === props.selectedActorId);
    }
  });
}

function renderCanvasScene(): void {
  const canvas = canvasRef.value;

  if (canvas === null || canvasSize.value.width <= 0 || canvasSize.value.height <= 0) {
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

  projectedCells.forEach((projectedCell) => {
    drawCell(context, projectedCell);
  });

  drawSceneEntities(context, projectedCells);
}

function scheduleCanvasRender(): void {
  if (renderFrameId.value !== null) {
    cancelAnimationFrame(renderFrameId.value);
  }

  renderFrameId.value = window.requestAnimationFrame(() => {
    renderCanvasScene();
  });
}

function startMovementAnimation(actorId: number, fromX: number, fromY: number, toX: number, toY: number): void {
  movementAnimations.value = {
    ...movementAnimations.value,
    [actorId]: {
      actorId,
      fromX,
      fromY,
      progress: 0,
      startedAt: performance.now(),
      toX,
      toY,
    },
  };

  tickMovementAnimation();
}

function tickMovementAnimation(): void {
  if (animationFrameId.value !== null) {
    cancelAnimationFrame(animationFrameId.value);
  }

  const nextAnimations: Record<number, MovementAnimation> = {};
  const now = performance.now();

  Object.values(movementAnimations.value).forEach((animation) => {
    const elapsed = now - animation.startedAt;
    const progress = Math.min(1, elapsed / ANIMATION_DURATION);

    if (progress < 1) {
      nextAnimations[animation.actorId] = {
        ...animation,
        progress,
      };
    }
  });

  movementAnimations.value = nextAnimations;
  scheduleCanvasRender();

  if (Object.keys(nextAnimations).length === 0) {
    animationFrameId.value = null;
    return;
  }

  animationFrameId.value = window.requestAnimationFrame(tickMovementAnimation);
}

function syncMovementAnimations(): void {
  props.runtimeScene.actor_instances.forEach((actor) => {
    const previousPosition = previousActorPositions.get(actor.id);

    if (
      previousPosition
      && previousPosition.x !== null
      && previousPosition.y !== null
      && actor.x !== null
      && actor.y !== null
      && (previousPosition.x !== actor.x || previousPosition.y !== actor.y)
    ) {
      startMovementAnimation(actor.id, previousPosition.x, previousPosition.y, actor.x, actor.y);
    }

    previousActorPositions.set(actor.id, {
      x: actor.x,
      y: actor.y,
    });
  });

  [...previousActorPositions.keys()].forEach((actorId) => {
    if (!props.runtimeScene.actor_instances.some((actor) => actor.id === actorId)) {
      previousActorPositions.delete(actorId);
    }
  });
}

function setupCanvasSize(): void {
  const canvas = canvasRef.value;
  const viewportElement = canvasViewportRef.value;

  if (canvas === null || viewportElement === null) {
    return;
  }

  const width = Math.max(1, Math.round(viewportElement.clientWidth));
  const height = Math.max(1, Math.round(viewportElement.clientHeight));
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

  if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) {
    return null;
  }

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

async function handleCellClick(x: number, y: number): Promise<void> {
  emit('update:selectedCellKey', resolveCellKey(x, y));

  const actorAtCell = getActorAtCell(x, y);

  if (actorAtCell) {
    if (canSelectActor(actorAtCell)) {
      emit('update:selectedActorId', actorAtCell.id);
    }

    scheduleCanvasRender();
    return;
  }

  if (selectedActor.value === null || selectedActor.value.x === null || selectedActor.value.y === null || !canMoveActor(selectedActor.value)) {
    scheduleCanvasRender();
    return;
  }

  emit('move-actor', {
    actor: selectedActor.value,
    x,
    y,
  });
}

function handleCanvasMouseDown(event: MouseEvent): void {
  if (event.button === 1) {
    event.preventDefault();
    pointerMode.value = 'rotate';
  } else if (event.button === 0) {
    pointerMode.value = 'pan';
  } else {
    return;
  }

  pointerStartX.value = event.clientX;
  pointerStartY.value = event.clientY;
  pointerStartOffsetX.value = viewport.value.offsetX;
  pointerStartOffsetY.value = viewport.value.offsetY;
  pointerStartRotateX.value = viewport.value.rotateX;
  pointerStartRotateZ.value = viewport.value.rotateZ;
  hasMoved.value = false;
}

function handleGlobalMouseMove(event: MouseEvent): void {
  const deltaX = event.clientX - pointerStartX.value;
  const deltaY = event.clientY - pointerStartY.value;

  if (pointerMode.value !== null && (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4)) {
    hasMoved.value = true;
  }

  if (pointerMode.value === 'pan') {
    viewport.value = {
      ...viewport.value,
      offsetX: pointerStartOffsetX.value + deltaX,
      offsetY: pointerStartOffsetY.value + deltaY,
    };
    scheduleCanvasRender();
    return;
  }

  if (pointerMode.value === 'rotate') {
    viewport.value = {
      ...viewport.value,
      rotateX: Math.min(MAX_VIEWPORT_ROTATE_X, Math.max(MIN_VIEWPORT_ROTATE_X, pointerStartRotateX.value - (deltaY * 0.14))),
      rotateZ: Math.min(MAX_VIEWPORT_ROTATE_Z, Math.max(MIN_VIEWPORT_ROTATE_Z, pointerStartRotateZ.value + (deltaX * 0.22))),
    };
    scheduleCanvasRender();
    return;
  }

  const point = resolveCanvasPoint(event);

  if (point === null) {
    if (hoveredCellKey.value !== null) {
      hoveredCellKey.value = null;
      scheduleCanvasRender();
    }

    return;
  }

  const hoveredCell = findCellAtCanvasPoint(point);
  const nextHoveredCellKey = hoveredCell ? resolveCellKey(hoveredCell.cell.x, hoveredCell.cell.y) : null;

  if (hoveredCellKey.value !== nextHoveredCellKey) {
    hoveredCellKey.value = nextHoveredCellKey;
    scheduleCanvasRender();
  }
}

function handleGlobalMouseUp(event: MouseEvent): void {
  const activePointerMode = pointerMode.value;
  pointerMode.value = null;

  if (activePointerMode === null) {
    return;
  }

  if (event.button === 1) {
    event.preventDefault();
  }

  if (event.button === 1 || hasMoved.value) {
    return;
  }

  const clickedPoint = resolveCanvasPoint(event);

  if (clickedPoint === null) {
    emit('update:selectedCellKey', null);
    scheduleCanvasRender();
    return;
  }

  const clickedCell = findCellAtCanvasPoint(clickedPoint);

  if (clickedCell !== null) {
    void handleCellClick(clickedCell.cell.x, clickedCell.cell.y);
    return;
  }

  emit('update:selectedCellKey', null);
  scheduleCanvasRender();
}

function handleCanvasMouseLeave(): void {
  hoveredCellKey.value = null;
  scheduleCanvasRender();
}

function handleCanvasWheel(event: WheelEvent): void {
  event.preventDefault();
  const nextZoom = event.deltaY > 0 ? viewport.value.zoom - 0.08 : viewport.value.zoom + 0.08;
  viewport.value = {
    ...viewport.value,
    zoom: Math.min(1.8, Math.max(0.45, Number.parseFloat(nextZoom.toFixed(2)))),
  };
  scheduleCanvasRender();
}

watch(
  () => props.runtimeScene.id,
  async () => {
    initializeViewport();
    await nextTick();
    setupCanvasSize();
    scheduleCanvasRender();
  },
  { immediate: true },
);

watch(
  () => props.runtimeScene.actor_instances.map((actor) => `${actor.id}:${actor.x}:${actor.y}`).join('|'),
  () => {
    syncMovementAnimations();
    scheduleCanvasRender();
  },
  { immediate: true },
);

watch(
  () => [
    props.runtimeScene.version,
    props.selectedActorId,
    props.selectedCellKey,
    hoveredCellKey.value,
    viewport.value.offsetX,
    viewport.value.offsetY,
    viewport.value.rotateX,
    viewport.value.rotateZ,
    viewport.value.zoom,
  ],
  () => {
    scheduleCanvasRender();
  },
);

onMounted(async () => {
  initializeViewport();
  await nextTick();
  setupCanvasSize();
  setupCanvasResizeObserver();
  window.addEventListener('resize', setupCanvasSize);
  window.addEventListener('mousemove', handleGlobalMouseMove);
  window.addEventListener('mouseup', handleGlobalMouseUp);
});

onBeforeUnmount(() => {
  resizeObserver?.disconnect();
  window.removeEventListener('resize', setupCanvasSize);
  window.removeEventListener('mousemove', handleGlobalMouseMove);
  window.removeEventListener('mouseup', handleGlobalMouseUp);

  if (renderFrameId.value !== null) {
    cancelAnimationFrame(renderFrameId.value);
  }

  if (animationFrameId.value !== null) {
    cancelAnimationFrame(animationFrameId.value);
  }
});
</script>

<template>
  <div
    ref="canvasViewportRef"
    class="runtime-scene-canvas-shell"
    @contextmenu.prevent
    @mousedown="handleCanvasMouseDown"
    @mouseleave="handleCanvasMouseLeave"
    @wheel="handleCanvasWheel"
  >
    <canvas ref="canvasRef" class="runtime-scene-canvas" />
  </div>
</template>

<style scoped>
.runtime-scene-canvas-shell {
  position: absolute;
  inset: 0;
  overflow: hidden;
  background:
    radial-gradient(circle at top, rgba(248, 216, 155, 0.08), transparent 32%),
    linear-gradient(180deg, rgba(17, 24, 39, 0.94), rgba(3, 7, 18, 0.98));
  cursor: crosshair;
}

.runtime-scene-canvas {
  display: block;
  width: 100%;
  height: 100%;
  cursor: crosshair;
}
</style>

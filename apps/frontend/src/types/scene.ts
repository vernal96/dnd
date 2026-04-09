import type { GameActor } from '@/types/actor';

export type SceneCell = {
  blocks_vision: boolean;
  elevation: number;
  id?: number;
  is_passable: boolean;
  props: Record<string, unknown> | null;
  scene_template_id?: number;
  terrain_type: string;
  x: number;
  y: number;
};

export type SceneSurfaceDefinition = {
  blocks_vision: boolean;
  code: 'fire' | 'grass' | 'ice' | 'poison' | 'soil' | 'stone' | 'water';
  image_url: string | null;
  is_passable: boolean;
  name: string;
  tags: string[];
};

export type SceneObject = {
  height: number;
  id?: number;
  is_hidden: boolean;
  is_interactive: boolean;
  kind: 'barrel' | 'bush' | 'house';
  name: string | null;
  state: Record<string, unknown> | null;
  width: number;
  x: number | null;
  y: number | null;
};

export type SceneObjectDefinition = {
  blocks_vision: boolean;
  code: 'barrel' | 'bush' | 'house';
  height: number;
  image_url: string | null;
  is_interactive: boolean;
  is_passable: boolean;
  name: string;
  tags: string[];
  width: number;
};

export type SceneViewportMetadata = {
  offsetX: number;
  offsetY: number;
  rotateX: number;
  rotateZ: number;
  zoom: number;
};

export type ScenePlayerSpawnPoint = {
  x: number;
  y: number;
};

export type SceneActorPlacement = {
  actor: GameActor;
  actor_id: number;
  id?: number;
  x: number;
  y: number;
};

export type SceneTemplateDetail = {
  actor_placements: SceneActorPlacement[];
  cells: SceneCell[];
  created_at: string;
  created_by: number | null;
  description: string | null;
  height: number;
  id: number;
  metadata: {
    player_spawn_point?: ScenePlayerSpawnPoint | null;
    viewport?: Partial<SceneViewportMetadata>;
  } | null;
  name: string;
  objects: SceneObject[];
  status: string;
  updated_at: string;
  width: number;
};

export type GameSceneDetail = {
  game: {
    active_scene_state_id: number | null;
    gm_user_id: number;
    id: number;
    title: string;
  };
  game_id: number;
  id: number;
  scene_template: SceneTemplateDetail;
  scene_template_id: number;
  status: string;
  version: number;
};

export type CreateScenePayload = {
  description?: string;
  height?: number;
  metadata?: Record<string, unknown> | null;
  name: string;
  width?: number;
};

export type UpdateScenePayload = {
  actors: Array<{
    actor_id: number;
    x: number;
    y: number;
  }>;
  cells: SceneCell[];
  description: string;
  height: number;
  metadata: {
    player_spawn_point?: ScenePlayerSpawnPoint | null;
    viewport: SceneViewportMetadata;
  };
  name: string;
  objects: SceneObject[];
  width: number;
};

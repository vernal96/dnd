import type { SceneCell, SceneObject, SceneTemplateDetail, SceneViewportMetadata } from '@/types/scene';

export type RuntimeActorInventoryItem = {
  isEquipped: boolean;
  itemCode: string;
  quantity: number;
  slot: string | null;
  state: Record<string, unknown> | null;
};

export type RuntimeEncounterParticipant = {
  action_available: boolean;
  actor: RuntimeActorInstance | null;
  actor_id: number;
  bonus_action_available: boolean;
  combat_result_state: string | null;
  id: number;
  initiative: number | null;
  joined_round: number | null;
  movement_left: number | null;
  reaction_available: boolean;
  turn_order: number | null;
};

export type RuntimeEncounterDetail = {
  current_participant_id: number | null;
  id: number;
  participants: RuntimeEncounterParticipant[];
  round: number;
  started_at: string | null;
  status: string;
};

export type RuntimeActorInstance = {
  controlled_by_user_id?: number | null;
  controller_type: string;
  game_id: number;
  game_scene_state_id: number | null;
  hp_current: number | null;
  hp_max: number | null;
  id: number;
  image_url: string | null;
  initiative: number | null;
  is_hidden: boolean;
  kind: 'npc' | 'player_character';
  movement_speed: number | null;
  name: string;
  runtime_state: {
    character_class?: string | null;
    image_path?: string | null;
    image_url?: string | null;
    inventory?: RuntimeActorInventoryItem[] | null;
    level?: number | null;
    movement_speed?: number | null;
    race?: string | null;
    source_actor_id?: number | null;
    stats?: Record<string, number> | null;
  } | null;
  status: string;
  x: number | null;
  y: number | null;
};

export type RuntimeSceneTemplate = Pick<
  SceneTemplateDetail,
  'cells' | 'created_at' | 'created_by' | 'description' | 'height' | 'id' | 'metadata' | 'name' | 'status' | 'updated_at' | 'width'
> & {
  objects: SceneObject[];
};

export type RuntimeSceneDetail = {
  actor_instances: RuntimeActorInstance[];
  encounter: RuntimeEncounterDetail | null;
  game: {
    active_scene_state_id: number | null;
    gm_user_id: number;
    id: number;
    status: string;
    title: string;
  };
  game_id: number;
  id: number;
  runtime_state: {
    activated_at?: string;
  } | null;
  item_drops: Array<{
    id: string;
    image_url?: string | null;
    item_code: string;
    name: string;
    quantity: number;
    x: number;
    y: number;
  }>;
  scene_template: RuntimeSceneTemplate;
  scene_template_id: number;
  status: string;
  version: number;
};

export type RuntimeActorMovePayload = {
  x: number;
  y: number;
};

export type RuntimeSceneViewport = SceneViewportMetadata;

export type RuntimeRenderableCell = SceneCell;

export type ActorInventoryItem = {
    isEquipped: boolean;
    itemCode: string;
    quantity: number;
    slot: string | null;
    state: Record<string, unknown> | null;
};

export type GameActor = {
    base_health: number | null;
    character_class: string | null;
    description: string | null;
    gm_user_id: number;
    health_current: number | null;
    health_max: number | null;
    armor_class: number;
    jump_height: number;
    id: number;
    image_path: string | null;
    image_url: string | null;
    inventory: ActorInventoryItem[] | null;
    kind: 'npc' | 'player_character';
    level: number;
    movement_speed: number;
    name: string;
    race: string | null;
    stats: Record<string, number> | null;
};

export type SaveGameActorPayload = {
    base_health: number | null;
    character_class: string | null;
    description: string | null;
    health_current: number | null;
    health_max: number | null;
    armor_class: number;
    jump_height: number;
    image_path: string | null;
    inventory: Array<{
        is_equipped: boolean;
        item_code: string;
        quantity: number;
        slot: string | null;
        state: Record<string, unknown> | null;
    }>;
    kind: 'npc';
    level: number;
    movement_speed: number;
    name: string;
    race: string | null;
    stats: Record<string, number>;
};

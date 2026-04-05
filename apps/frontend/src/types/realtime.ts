export type RealtimeEventType =
    | 'game-scene.activated'
    | 'game-scene.actor-moved'
    | 'game-scene.actor-spawned'
    | 'game-scene.cell-painted'
    | 'game-scene.item-dropped'
    | 'game-scene.updated'
    | 'game-invitation.accepted'
    | 'game-invitation.created'
    | 'game-invitation.declined';

export type RealtimeEventPayload = {
    activeSceneStateId?: number;
    actor?: Record<string, unknown>;
    cell?: {
        blocks_vision: boolean;
        is_passable: boolean;
        terrain_type: string;
        x: number;
        y: number;
    };
    gameId: number;
    gmUserId?: number;
    invitationId?: number;
    invitedUserId?: number;
    itemDrop?: {
        id: string;
        item_code: string;
        name: string;
        quantity: number;
        x: number;
        y: number;
    };
    sceneName?: string | null;
    sceneStateId?: number;
    status?: string;
    token?: string;
    version?: number;
};

export type RealtimeEventMessage = {
    event: RealtimeEventType;
    payload: RealtimeEventPayload;
};

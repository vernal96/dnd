export type GameMasterSummary = {
    email: string;
    id: number;
    name: string;
};

export type GameMemberSummary = {
    id: number;
    joined_at: string | null;
    player_character: null | {
        class: string | null;
        created_at: string | null;
        description: string | null;
        experience: number;
        id: number;
        image_path: string | null;
        image_url: string | null;
        level: number;
        name: string;
        race: string | null;
        status: string;
        subrace: string | null;
        updated_at: string | null;
        user_id: number;
    };
    role: string;
    status: string;
    user: GameMasterSummary;
};

export type GameInvitationSummary = {
    game: Pick<GameSummary, 'description' | 'id' | 'status' | 'title'>;
    gm: GameMasterSummary;
    id: number;
    invited_user_id: number;
    responded_at: string | null;
    sent_at: string | null;
    status: string;
    token: string;
};

export type GameSummary = {
    created_at: string;
    description: string | null;
    gm: GameMasterSummary;
    gm_user_id: number;
    id: number;
    members_count: number;
    status: string;
    title: string;
    updated_at: string;
};

export type GameStatus = 'active' | 'completed' | 'draft' | 'paused';

export type GameSceneSummary = {
    created_at: string;
    game_id: number;
    id: number;
    scene_template: {
        created_at: string;
        description: string | null;
        height: number;
        id: number;
        metadata: Record<string, unknown> | null;
        name: string;
        status: string;
        updated_at: string;
        width: number;
    };
    status: string;
    updated_at: string;
    version: number;
};

export type GameDetail = GameSummary & {
    active_scene_state_id: number | null;
    invitations: Array<{
        id: number;
        invited_user: GameMasterSummary;
        responded_at: string | null;
        sent_at: string | null;
        status: string;
        token: string;
    }>;
    members: GameMemberSummary[];
    scene_states: GameSceneSummary[];
    settings: Record<string, unknown> | null;
};

export type PlayerActiveGameSummary = Pick<GameSummary, 'description' | 'id' | 'status' | 'title'> & {
    active_scene_state: null | {
        id: number;
        loaded_at: string | null;
        scene_template: {
            height: number;
            id: number;
            name: string;
            status: string;
            width: number;
        } | null;
        scene_template_id: number;
        status: string;
        version: number;
    };
    active_scene_state_id: number;
    gm: GameMasterSummary;
    members: GameMemberSummary[];
};

export type PaginatedGamesResponse = {
    current_page: number;
    data: GameSummary[];
    last_page: number;
    per_page: number;
    total: number;
};

export type CreateGamePayload = {
    description: string;
    title: string;
};

export type InviteGameMemberPayload = {
    login: string;
};

export type GameStatusFilter = 'active' | 'all' | 'completed' | 'draft' | 'paused';

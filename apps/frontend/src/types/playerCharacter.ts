export type CharacterStats = {
    cha: number;
    con: number;
    dex: number;
    int: number;
    str: number;
    wis: number;
};

export type PlayerCharacter = {
    active_game_id: number | null;
    active_game_title: string | null;
    base_stats: CharacterStats | null;
    character_class: string | null;
    character_class_name: string | null;
    created_at: string | null;
    description: string | null;
    experience: number;
    id: number;
    image_path: string | null;
    image_url: string | null;
    is_available_for_join: boolean;
    level: number;
    name: string;
    race: string | null;
    race_name: string | null;
    status: string;
    subrace: string | null;
    subrace_name: string | null;
    updated_at: string | null;
    user_id: number;
    derived_stats: CharacterStats | null;
};

export type CreatePlayerCharacterPayload = {
    base_stats: CharacterStats;
    character_class: string;
    description: string | null;
    image_path: string | null;
    name: string;
    race: string;
    subrace: string | null;
};

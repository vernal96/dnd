export type SubraceDefinition = {
    abilityBonuses: Record<string, number>;
    code: string;
    description: string | null;
    isActive: boolean;
    name: string;
};

export type RaceDefinition = {
    abilityBonuses: Record<string, number>;
    abilityBonusChoices: Array<{ abilities: string[]; count: number; value: number }>;
    code: string;
    description: string | null;
    isActive: boolean;
    isPlayerSelectable: boolean;
    name: string;
    subraces: SubraceDefinition[];
};

export type CharacterClassDefinition = {
    abilityBonuses: Record<string, number>;
    code: string;
    defaultPointBuyAllocation: Record<string, number>;
    description: string | null;
    isActive: boolean;
    isPlayerSelectable: boolean;
    name: string;
    primaryAbilities: CharacterAbilityDefinition[];
};

export type CharacterAbilityDefinition = {
    code: 'str' | 'dex' | 'con' | 'int' | 'wis' | 'cha';
    defaultValue: number;
    description: string | null;
    name: string;
};

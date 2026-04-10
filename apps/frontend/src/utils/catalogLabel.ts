const RACE_LABELS: Record<string, string> = {
  aasimar: 'Аазимар',
  dragonborn: 'Драконорождённый',
  dwarf: 'Дварф',
  elf: 'Эльф',
  genasi: 'Генази',
  gnome: 'Гном',
  goliath: 'Голиаф',
  'half-elf': 'Полуэльф',
  'half-orc': 'Полуорк',
  halfling: 'Полурослик',
  human: 'Человек',
  orc: 'Орк',
  tiefling: 'Тифлинг',
};

const CHARACTER_CLASS_LABELS: Record<string, string> = {
  artificer: 'Изобретатель',
  barbarian: 'Варвар',
  bard: 'Бард',
  cleric: 'Жрец',
  druid: 'Друид',
  fighter: 'Воин',
  monk: 'Монах',
  paladin: 'Паладин',
  ranger: 'Следопыт',
  rogue: 'Плут',
  sorcerer: 'Чародей',
  warlock: 'Колдун',
  wizard: 'Волшебник',
};

function hasCyrillic(value: string): boolean {
  return /[А-Яа-яЁё]/.test(value);
}

function normalizeCode(value: string | null | undefined): string | null {
  if (typeof value !== 'string') {
    return null;
  }

  const normalizedValue = value.trim();

  return normalizedValue === '' ? null : normalizedValue.toLowerCase();
}

function prettifyCode(value: string): string {
  return value
    .split('-')
    .filter((part) => part !== '')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

export function resolveRaceLabel(value: string | null | undefined, fallback = 'Без расы'): string {
  const normalizedValue = normalizeCode(value);

  if (normalizedValue === null) {
    return fallback;
  }

  if (hasCyrillic(value ?? '')) {
    return value ?? fallback;
  }

  return RACE_LABELS[normalizedValue] ?? prettifyCode(normalizedValue);
}

export function resolveCharacterClassLabel(value: string | null | undefined, fallback = 'Без класса'): string {
  const normalizedValue = normalizeCode(value);

  if (normalizedValue === null) {
    return fallback;
  }

  if (hasCyrillic(value ?? '')) {
    return value ?? fallback;
  }

  return CHARACTER_CLASS_LABELS[normalizedValue] ?? prettifyCode(normalizedValue);
}

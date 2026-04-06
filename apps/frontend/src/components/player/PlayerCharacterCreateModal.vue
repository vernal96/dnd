<script setup lang="ts">
import { ChevronLeft, ChevronRight, ImagePlus, Plus, Sparkles, Upload, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { createGameActor } from '@/services/actorApi';
import { uploadActorImage } from '@/services/actorImageApi';
import { fetchCharacterAbilities } from '@/services/characterAbilityApi';
import { fetchCharacterClasses } from '@/services/characterClassApi';
import { createPlayerCharacter } from '@/services/playerCharacterApi';
import { uploadPlayerCharacterImage } from '@/services/playerCharacterImageApi';
import { fetchRaces } from '@/services/raceApi';
import type { GameActor } from '@/types/actor';
import type { CharacterAbilityDefinition, CharacterClassDefinition, RaceDefinition } from '@/types/catalog';
import type { PlayerCharacter } from '@/types/playerCharacter';

const props = withDefaults(defineProps<{
  mode?: 'npc' | 'player';
  open: boolean;
}>(), {
  mode: 'player',
});

const emit = defineEmits<{
  actorCreated: [actor: GameActor];
  close: [];
  created: [character: PlayerCharacter];
}>();

const POINT_BUY_POOL = 27;
const MAX_IMAGE_SIZE_BYTES = 16 * 1024 * 1024;

const step = ref(1);
const isLoading = ref(false);
const isSaving = ref(false);
const isUploadingImage = ref(false);
const modalError = ref('');
const races = ref<RaceDefinition[]>([]);
const classes = ref<CharacterClassDefinition[]>([]);
const abilities = ref<CharacterAbilityDefinition[]>([]);
const selectedRaceCode = ref('');
const selectedSubraceCode = ref('');
const selectedClassCode = ref('');
const characterName = ref('');
const characterDescription = ref('');
const imagePath = ref<string | null>(null);
const imagePreviewUrl = ref<string | null>(null);
const allocatedAbilityPoints = ref<Record<string, number>>({});

const isNpcMode = computed<boolean>(() => props.mode === 'npc');
const selectedRace = computed<RaceDefinition | null>(() => races.value.find((race) => race.code === selectedRaceCode.value) ?? null);
const selectedClass = computed<CharacterClassDefinition | null>(() => classes.value.find((characterClass) => characterClass.code === selectedClassCode.value) ?? null);
const abilityBonuses = computed<Record<string, number>>(() => {
  const bonuses: Record<string, number> = {
    str: 0,
    dex: 0,
    con: 0,
    int: 0,
    wis: 0,
    cha: 0,
  };

  for (const [code, value] of Object.entries(selectedRace.value?.abilityBonuses ?? {})) {
    bonuses[code] = (bonuses[code] ?? 0) + value;
  }

  const selectedSubrace = selectedRace.value?.subraces.find((subrace) => subrace.code === selectedSubraceCode.value) ?? null;

  for (const [code, value] of Object.entries(selectedSubrace?.abilityBonuses ?? {})) {
    bonuses[code] = (bonuses[code] ?? 0) + value;
  }

  for (const [code, value] of Object.entries(selectedClass.value?.abilityBonuses ?? {})) {
    bonuses[code] = (bonuses[code] ?? 0) + value;
  }

  return bonuses;
});
const pointsSpent = computed<number>(() => Object.values(allocatedAbilityPoints.value).reduce((total, value) => total + value, 0));
const pointsRemaining = computed<number>(() => POINT_BUY_POOL - pointsSpent.value);
const displayedAbilityValues = computed<Record<string, number>>(() =>
  Object.fromEntries(
    abilities.value.map((ability) => [
      ability.code,
      ability.defaultValue + (abilityBonuses.value[ability.code] ?? 0) + (allocatedAbilityPoints.value[ability.code] ?? 0),
    ]),
  ) as Record<string, number>,
);
const canAutoAllocate = computed<boolean>(() => step.value === 3 && selectedClass.value !== null);

/**
 * Форматирует бонусы характеристик для отображения под описанием.
 */
function formatAbilityBonuses(bonuses: Record<string, number>): string {
  const entries = abilities.value
    .map((ability) => ({
      code: ability.code,
      name: ability.name,
      value: bonuses[ability.code] ?? 0,
    }))
    .filter((entry) => entry.value > 0);

  if (entries.length === 0) {
    return '';
  }

  return entries.map((entry) => `${entry.name} +${entry.value}`).join(', ');
}

/**
 * Форматирует основные характеристики класса.
 */
function formatPrimaryAbilities(primaryAbilities: CharacterAbilityDefinition[]): string {
  return primaryAbilities.map((ability) => ability.name).join(', ');
}

const constructorLabels = computed(() => (isNpcMode.value
  ? {
      createAction: 'Создать NPC',
      finishTitle: 'Заверши NPC',
      profileTitle: 'Профиль NPC',
      title: 'Новый NPC',
      wizardTitle: 'Создание NPC',
    }
  : {
      createAction: 'Создать героя',
      finishTitle: 'Заверши профиль',
      profileTitle: 'Профиль героя',
      title: 'Новый персонаж',
      wizardTitle: 'Создание героя',
    }));
const canProceed = computed<boolean>(() => {
  if (step.value === 1) {
    if (selectedRace.value === null) {
      return false;
    }

    return selectedRace.value.subraces.length === 0 || selectedSubraceCode.value !== '';
  }

  if (step.value === 2) {
    return selectedClassCode.value !== '';
  }

  if (step.value === 3) {
    return pointsRemaining.value === 0;
  }

  return characterName.value.trim().length >= 2;
});

/**
 * Сбрасывает форму создания персонажа.
 */
function resetForm(): void {
  step.value = 1;
  modalError.value = '';
  selectedRaceCode.value = '';
  selectedSubraceCode.value = '';
  selectedClassCode.value = '';
  characterName.value = '';
  characterDescription.value = '';
  imagePath.value = null;
  imagePreviewUrl.value = null;
  allocatedAbilityPoints.value = Object.fromEntries(abilities.value.map((ability) => [ability.code, 0])) as Record<string, number>;
}

/**
 * Загружает серверные справочники мастера создания героя.
 */
async function loadCatalogs(): Promise<void> {
  isLoading.value = true;
  modalError.value = '';

  try {
    const [nextRaces, nextClasses, nextAbilities] = await Promise.all([
      fetchRaces(),
      fetchCharacterClasses(),
      fetchCharacterAbilities(),
    ]);

    races.value = nextRaces;
    classes.value = nextClasses;
    abilities.value = nextAbilities;
    allocatedAbilityPoints.value = Object.fromEntries(nextAbilities.map((ability) => [ability.code, 0])) as Record<string, number>;
  } catch (error) {
    modalError.value = (error as Error).message;
  } finally {
    isLoading.value = false;
  }
}

/**
 * Переходит к следующему шагу мастера.
 */
function goToNextStep(): void {
  if (!canProceed.value || step.value >= 4) {
    return;
  }

  step.value += 1;
}

/**
 * Возвращает пользователя на предыдущий шаг мастера.
 */
function goToPreviousStep(): void {
  if (step.value <= 1) {
    return;
  }

  step.value -= 1;
}

/**
 * Изменяет значение характеристики на один пункт в пределах доступного бюджета.
 */
function adjustAbility(code: string, delta: number): void {
  const currentValue = allocatedAbilityPoints.value[code] ?? 0;
  const nextValue = currentValue + delta;

  if (nextValue < 0) {
    return;
  }

  if (delta > 0 && pointsRemaining.value <= 0) {
    return;
  }

  allocatedAbilityPoints.value = {
    ...allocatedAbilityPoints.value,
    [code]: nextValue,
  };
}

/**
 * Автоматически распределяет 27 очков по рекомендациям выбранного класса.
 */
function applyAutomaticAllocation(): void {
  if (selectedClass.value === null) {
    return;
  }

  allocatedAbilityPoints.value = Object.fromEntries(
    abilities.value.map((ability) => [
      ability.code,
      selectedClass.value?.defaultPointBuyAllocation?.[ability.code] ?? 0,
    ]),
  ) as Record<string, number>;
}

/**
 * Обрабатывает загрузку фото героя.
 */
async function handleImageSelected(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];

  if (!file) {
    return;
  }

  if (file.size > MAX_IMAGE_SIZE_BYTES) {
    modalError.value = 'Фото должно быть не больше 16 МБ.';
    input.value = '';

    return;
  }

  isUploadingImage.value = true;
  modalError.value = '';

  try {
    if (isNpcMode.value) {
      const uploadedImage = await uploadActorImage(file);
      imagePath.value = `gm-actors/${uploadedImage.fileName}`;
      imagePreviewUrl.value = uploadedImage.downloadUrl;
    } else {
      const uploadedImage = await uploadPlayerCharacterImage(file);
      imagePath.value = uploadedImage.storagePath;
      imagePreviewUrl.value = uploadedImage.downloadUrl;
    }
  } catch (error) {
    modalError.value = (error as Error).message;
  } finally {
    isUploadingImage.value = false;
    input.value = '';
  }
}

/**
 * Создает персонажа текущего игрока.
 */
async function handleCreateCharacter(): Promise<void> {
  if (!canProceed.value || step.value !== 4) {
    return;
  }

  isSaving.value = true;
  modalError.value = '';

  try {
    if (isNpcMode.value) {
      const actor = await createGameActor({
        kind: 'npc',
        name: characterName.value.trim(),
        description: characterDescription.value.trim() || null,
        race: selectedRaceCode.value,
        character_class: selectedClassCode.value,
        image_path: imagePath.value,
        level: 1,
        movement_speed: 6,
        base_health: 10,
        health_current: 10,
        health_max: 10,
        stats: {
          strength: displayedAbilityValues.value.str ?? 1,
          dexterity: displayedAbilityValues.value.dex ?? 1,
          constitution: displayedAbilityValues.value.con ?? 1,
          intelligence: displayedAbilityValues.value.int ?? 1,
          wisdom: displayedAbilityValues.value.wis ?? 1,
          charisma: displayedAbilityValues.value.cha ?? 1,
        },
        inventory: [],
      });

      emit('actorCreated', actor);
    } else {
      const character = await createPlayerCharacter({
        name: characterName.value.trim(),
        description: characterDescription.value.trim() || null,
        race: selectedRaceCode.value,
        subrace: selectedSubraceCode.value || null,
        character_class: selectedClassCode.value,
        image_path: imagePath.value,
        base_stats: {
          str: displayedAbilityValues.value.str ?? 1,
          dex: displayedAbilityValues.value.dex ?? 1,
          con: displayedAbilityValues.value.con ?? 1,
          int: displayedAbilityValues.value.int ?? 1,
          wis: displayedAbilityValues.value.wis ?? 1,
          cha: displayedAbilityValues.value.cha ?? 1,
        },
      });

      emit('created', character);
    }

    emit('close');
  } catch (error) {
    modalError.value = (error as Error).message;
  } finally {
    isSaving.value = false;
  }
}

watch(
  () => props.open,
  async (isOpen) => {
    if (!isOpen) {
      return;
    }

    if (races.value.length === 0 || classes.value.length === 0 || abilities.value.length === 0) {
      await loadCatalogs();
    }

    resetForm();
  },
);
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-6 backdrop-blur-sm"
    >
      <div class="absolute inset-0" @click="emit('close')" />

      <section class="relative z-10 flex h-[min(54rem,calc(100vh-3rem))] w-full max-w-6xl overflow-hidden rounded-[2rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(17,24,39,0.98),rgba(2,6,23,0.98))] shadow-[0_30px_80px_rgba(2,6,23,0.65)]">
        <aside class="flex w-full max-w-xs flex-col border-r border-amber-200/10 bg-slate-950/40 p-6">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">
                {{ constructorLabels.wizardTitle }}
              </p>
              <h2 class="mt-2 font-display text-3xl text-amber-50">
                {{ constructorLabels.title }}
              </h2>
            </div>

            <button
              class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-slate-300 transition hover:border-amber-200/30 hover:text-amber-50"
              type="button"
              @click="emit('close')"
            >
              <X class="h-4 w-4" />
            </button>
          </div>

          <div class="mt-8 space-y-3">
            <div
              v-for="wizardStep in [
                { id: 1, title: 'Раса' },
                { id: 2, title: 'Класс' },
                { id: 3, title: 'Характеристики' },
                { id: 4, title: 'Профиль' },
              ]"
              :key="wizardStep.id"
              :class="step === wizardStep.id ? 'border-amber-300/30 bg-amber-300/10 text-amber-50' : 'border-amber-200/10 bg-white/5 text-slate-300'"
              class="rounded-[1.25rem] border px-4 py-3 text-sm"
            >
              <span class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Шаг {{ wizardStep.id }}</span>
              <p class="mt-1 font-medium">{{ wizardStep.title }}</p>
            </div>
          </div>

          <div class="mt-auto rounded-[1.4rem] border border-amber-200/10 bg-slate-950/40 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">
              Point Buy
            </p>
            <p class="mt-2 font-display text-3xl text-amber-50">
              {{ pointsRemaining }}
            </p>
            <p class="mt-2 text-sm leading-6 text-slate-300">
              Осталось очков для распределения характеристик.
            </p>
          </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
          <div class="flex items-center justify-between gap-4 border-b border-amber-200/10 p-6">
            <div>
              <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">
                Шаг {{ step }} из 4
              </p>
              <h3 class="mt-2 font-display text-3xl text-amber-50">
                {{
                  step === 1
                    ? 'Выбери расу'
                    : step === 2
                      ? 'Выбери класс'
                      : step === 3
                        ? 'Распредели характеристики'
                        : constructorLabels.finishTitle
                }}
              </h3>
            </div>

            <Sparkles class="h-5 w-5 text-amber-200" />
          </div>

          <div class="flex-1 overflow-y-auto p-6">
            <div
              v-if="modalError"
              class="mb-6 rounded-[1.25rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-100"
            >
              {{ modalError }}
            </div>

            <div
              v-if="isLoading"
              class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 px-5 py-8 text-sm text-slate-300"
            >
              Загружаем справочники создания героя...
            </div>

            <template v-else>
              <div
                v-if="step === 1"
                class="space-y-6"
              >
                <div class="grid gap-4 lg:grid-cols-2">
                  <button
                    v-for="race in races"
                    :key="race.code"
                    :class="selectedRaceCode === race.code ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-white/5'"
                    class="rounded-[1.5rem] border p-5 text-left transition hover:border-amber-200/30"
                    type="button"
                    @click="selectedRaceCode = race.code; selectedSubraceCode = race.subraces.length === 1 ? race.subraces[0].code : ''"
                  >
                    <p class="font-display text-2xl text-amber-50">{{ race.name }}</p>
                    <p class="mt-3 text-sm leading-7 text-slate-300">
                      {{ race.description || 'Описание расы пока не заполнено.' }}
                    </p>
                    <p
                      v-if="formatAbilityBonuses(race.abilityBonuses)"
                      class="mt-3 text-xs uppercase tracking-[0.18em] text-amber-200/60"
                    >
                      Бонусы: {{ formatAbilityBonuses(race.abilityBonuses) }}
                    </p>
                  </button>
                </div>

                <div
                  v-if="selectedRace && selectedRace.subraces.length > 0"
                  class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-5"
                >
                  <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">
                    Подраса
                  </p>
                  <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <button
                      v-for="subrace in selectedRace.subraces"
                      :key="subrace.code"
                      :class="selectedSubraceCode === subrace.code ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-slate-950/30'"
                      class="rounded-[1.25rem] border p-4 text-left transition hover:border-amber-200/30"
                      type="button"
                      @click="selectedSubraceCode = subrace.code"
                    >
                      <p class="text-sm font-medium text-amber-50">{{ subrace.name }}</p>
                      <p class="mt-2 text-sm leading-6 text-slate-300">
                        {{ subrace.description || 'Описание подрасы пока не заполнено.' }}
                      </p>
                      <p
                        v-if="formatAbilityBonuses(subrace.abilityBonuses)"
                        class="mt-3 text-xs uppercase tracking-[0.18em] text-amber-200/60"
                      >
                        Бонусы: {{ formatAbilityBonuses(subrace.abilityBonuses) }}
                      </p>
                    </button>
                  </div>
                </div>
              </div>

              <div
                v-else-if="step === 2"
                class="grid gap-4 lg:grid-cols-2"
              >
                <button
                  v-for="characterClass in classes"
                  :key="characterClass.code"
                  :class="selectedClassCode === characterClass.code ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-white/5'"
                  class="rounded-[1.5rem] border p-5 text-left transition hover:border-amber-200/30"
                  type="button"
                  @click="selectedClassCode = characterClass.code"
                >
                  <p class="font-display text-2xl text-amber-50">{{ characterClass.name }}</p>
                  <p class="mt-3 text-sm leading-7 text-slate-300">
                    {{ characterClass.description || 'Описание класса пока не заполнено.' }}
                  </p>
                  <p
                    v-if="formatAbilityBonuses(characterClass.abilityBonuses)"
                    class="mt-3 text-xs uppercase tracking-[0.18em] text-amber-200/60"
                  >
                    Бонусы: {{ formatAbilityBonuses(characterClass.abilityBonuses) }}
                  </p>
                  <p
                    v-if="characterClass.primaryAbilities.length > 0"
                    class="mt-2 text-xs uppercase tracking-[0.18em] text-sky-200/70"
                  >
                    Основные: {{ formatPrimaryAbilities(characterClass.primaryAbilities) }}
                  </p>
                </button>
              </div>

              <div
                v-else-if="step === 3"
                class="space-y-4"
              >
                <div class="flex justify-end">
                  <button
                    class="rounded-full border border-sky-300/25 bg-sky-500/10 px-4 py-2 text-sm text-sky-100 transition hover:border-sky-200/40 hover:bg-sky-500/15 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!canAutoAllocate"
                    type="button"
                    @click="applyAutomaticAllocation"
                  >
                    Автоматическое распределение
                  </button>
                </div>

                <div
                  v-for="ability in abilities"
                  :key="ability.code"
                  :class="selectedClass?.primaryAbilities.some((primaryAbility) => primaryAbility.code === ability.code) ? 'border-sky-300/30 bg-sky-500/10' : 'border-amber-200/10 bg-white/5'"
                  class="grid gap-4 rounded-[1.5rem] border p-5 md:grid-cols-[minmax(0,1fr)_auto]"
                >
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <p class="font-display text-2xl text-amber-50">{{ ability.name }}</p>
                      <span
                        v-if="selectedClass?.primaryAbilities.some((primaryAbility) => primaryAbility.code === ability.code)"
                        class="rounded-full border border-sky-300/30 bg-sky-500/15 px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-sky-100"
                      >
                        Основная
                      </span>
                    </div>
                    <p class="mt-2 text-sm leading-7 text-slate-300">
                      {{ ability.description || 'Описание характеристики пока не заполнено.' }}
                    </p>
                  </div>

                  <div class="flex items-center gap-3">
                    <button
                      class="rounded-full border border-amber-200/10 bg-slate-950/40 p-3 text-slate-200 transition hover:border-amber-200/30"
                      type="button"
                      @click="adjustAbility(ability.code, -1)"
                    >
                      <ChevronLeft class="h-4 w-4" />
                    </button>

                    <div class="min-w-20 rounded-[1.25rem] border border-amber-300/20 bg-amber-300/10 px-5 py-3 text-center">
                      <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">{{ ability.code.toUpperCase() }}</p>
                      <p class="mt-1 font-display text-3xl text-amber-50">{{ displayedAbilityValues[ability.code] ?? 1 }}</p>
                    </div>

                    <button
                      class="rounded-full border border-amber-200/10 bg-slate-950/40 p-3 text-slate-200 transition hover:border-amber-200/30 disabled:cursor-not-allowed disabled:opacity-40"
                      :disabled="pointsRemaining <= 0"
                      type="button"
                      @click="adjustAbility(ability.code, 1)"
                    >
                      <Plus class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </div>

              <div
                v-else
                class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]"
              >
                <div class="space-y-5">
                  <section class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">{{ constructorLabels.profileTitle }}</p>

                    <label class="mt-4 block">
                      <span class="text-xs uppercase text-amber-200/50">Имя</span>
                      <input v-model="characterName" class="mt-2 w-full rounded-2xl border border-amber-200/10 bg-slate-950/50 px-4 py-3 text-sm text-amber-50 outline-none transition focus:border-amber-300/30" maxlength="120" type="text">
                    </label>

                    <label class="mt-4 block">
                      <span class="text-xs uppercase text-amber-200/50">Описание</span>
                      <textarea v-model="characterDescription" class="mt-2 min-h-28 w-full rounded-2xl border border-amber-200/10 bg-slate-950/50 px-4 py-3 text-sm text-amber-50 outline-none transition focus:border-amber-300/30" maxlength="1500" />
                    </label>
                  </section>
                </div>

                <aside class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-5">
                  <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Фото</p>

                  <div class="mt-4 overflow-hidden rounded-[1.4rem] border border-amber-200/10 bg-slate-950/40">
                    <img
                      v-if="imagePreviewUrl"
                      :src="imagePreviewUrl"
                      alt="Фото героя"
                      class="h-64 w-full object-cover"
                    >
                    <div
                      v-else
                      class="flex h-64 items-center justify-center text-slate-400"
                    >
                      <ImagePlus class="h-10 w-10" />
                    </div>
                  </div>

                  <label class="mt-4 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-300/15">
                    <Upload class="h-4 w-4" />
                    {{ isUploadingImage ? 'Загружаем...' : 'Загрузить фото' }}
                    <input class="hidden" :disabled="isUploadingImage" type="file" accept="image/*" @change="handleImageSelected">
                  </label>
                </aside>
              </div>
            </template>
          </div>

          <div class="flex items-center justify-between gap-4 border-t border-amber-200/10 p-6">
            <button
              class="inline-flex items-center gap-2 rounded-full border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-amber-200/30 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="step === 1"
              type="button"
              @click="goToPreviousStep"
            >
              <ChevronLeft class="h-4 w-4" />
              Назад
            </button>

            <button
              v-if="step < 4"
              class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-300/15 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="!canProceed"
              type="button"
              @click="goToNextStep"
            >
              Далее
              <ChevronRight class="h-4 w-4" />
            </button>

            <button
              v-else
              class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-300/15 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="!canProceed || isSaving"
              type="button"
              @click="handleCreateCharacter"
            >
              {{ isSaving ? 'Создаем...' : constructorLabels.createAction }}
            </button>
          </div>
        </div>
      </section>
    </div>
  </Teleport>
</template>

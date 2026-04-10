<script setup lang="ts">
import {Check, Swords, X} from 'lucide-vue-next';
import {computed, ref, watch} from 'vue';
import {fetchInvitationAvailableCharacters} from '@/services/gameApi';
import type {GameInvitationSummary} from '@/types/game';
import type {PlayerCharacter} from '@/types/playerCharacter';
import {resolveCharacterClassLabel, resolveRaceLabel} from '@/utils/catalogLabel';

const props = defineProps<{
  open: boolean;
  pending: boolean;
  invitation: GameInvitationSummary | null;
}>();

const emit = defineEmits<{
  accept: [characterId: number];
  close: [];
}>();

const availableCharacters = ref<PlayerCharacter[]>([]);
const isLoading = ref(false);
const modalError = ref('');
const selectedCharacterId = ref<number | null>(null);

const selectedCharacter = computed<PlayerCharacter | null>(() => {
  if (selectedCharacterId.value === null) {
    return null;
  }

  return availableCharacters.value.find((character) => character.id === selectedCharacterId.value) ?? null;
});

async function loadAvailableCharacters(): Promise<void> {
  if (!props.open || props.invitation === null) {
    availableCharacters.value = [];
    selectedCharacterId.value = null;
    return;
  }

  isLoading.value = true;
  modalError.value = '';

  try {
    availableCharacters.value = await fetchInvitationAvailableCharacters(props.invitation.token);
    selectedCharacterId.value = availableCharacters.value[0]?.id ?? null;
  } catch (error) {
    modalError.value = (error as Error).message;
  } finally {
    isLoading.value = false;
  }
}

function handleAccept(): void {
  if (selectedCharacterId.value === null || props.pending) {
    return;
  }

  emit('accept', selectedCharacterId.value);
}

watch(
  () => [props.open, props.invitation?.token],
  () => {
    void loadAvailableCharacters();
  },
  {immediate: true},
);
</script>

<template>
  <teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
    >
      <div class="w-full max-w-4xl overflow-hidden rounded-[1.8rem] border border-amber-200/15 bg-[linear-gradient(180deg,rgba(20,12,25,0.98),rgba(37,22,50,0.96))] text-slate-100 shadow-[0_30px_80px_rgba(7,4,11,0.45)]">
        <div class="flex items-start justify-between gap-4 border-b border-amber-200/10 px-6 py-5">
          <div>
            <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">
              Принятие приглашения
            </p>
            <h2 class="mt-2 font-display text-3xl text-amber-50">
              {{ invitation?.game.title }}
            </h2>
            <p class="mt-2 text-sm leading-6 text-slate-300">
              Выбери героя, за которого войдешь в эту игру. Доступны только персонажи, которые сейчас не заняты в других незавершенных играх.
            </p>
          </div>

          <button
            class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-slate-300 transition hover:border-amber-200/30 hover:text-amber-50"
            type="button"
            @click="emit('close')"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_20rem]">
          <div class="border-b border-amber-200/10 p-6 lg:border-b-0 lg:border-r">
            <div
              v-if="modalError"
              class="rounded-[1.3rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-6 text-rose-100"
            >
              {{ modalError }}
            </div>

            <div
              v-else-if="isLoading"
              class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm text-slate-300"
            >
              Загружаем доступных героев...
            </div>

            <div
              v-else-if="availableCharacters.length === 0"
              class="rounded-2xl border border-amber-200/10 bg-slate-950/30 px-4 py-4 text-sm leading-6 text-slate-300"
            >
              У тебя нет свободных персонажей для входа в эту игру.
            </div>

            <div
              v-else
              class="grid gap-3"
            >
              <button
                v-for="character in availableCharacters"
                :key="character.id"
                :class="selectedCharacterId === character.id ? 'border-amber-300/40 bg-amber-300/10' : 'border-amber-200/10 bg-slate-950/30'"
                class="rounded-[1.35rem] border text-left transition hover:border-amber-200/30"
                type="button"
                @click="selectedCharacterId = character.id"
              >
                <div class="grid items-center gap-4 p-4 md:grid-cols-[4rem_minmax(0,1fr)_auto]">
                  <div class="overflow-hidden rounded-xl border border-amber-200/10 bg-slate-950/40">
                    <img
                      v-if="character.image_url"
                      :src="character.image_url"
                      :alt="character.name"
                      class="h-16 w-16 object-cover object-top"
                    >
                    <div
                      v-else
                      class="flex h-16 w-16 items-center justify-center text-2xl font-display text-amber-100/80"
                    >
                      {{ character.name.slice(0, 1) }}
                    </div>
                  </div>

                  <div class="min-w-0">
                    <p class="truncate font-display text-2xl text-amber-50">{{ character.name }}</p>
                    <p class="mt-1 truncate text-sm text-slate-300">
                      {{ character.race_name || resolveRaceLabel(character.race) }}
                      <template v-if="character.subrace_name"> · {{ character.subrace_name }}</template>
                      · {{ character.character_class_name || resolveCharacterClassLabel(character.character_class) }}
                      · ур. {{ character.level }}
                    </p>
                    <p class="mt-1 truncate text-xs text-slate-400">
                      {{ character.description || 'Описание героя пока не заполнено.' }}
                    </p>
                  </div>

                  <span
                    v-if="selectedCharacterId === character.id"
                    class="inline-flex items-center gap-1 rounded-full border border-emerald-300/20 bg-emerald-500/10 px-3 py-1 text-xs uppercase text-emerald-100"
                  >
                    <Check class="h-3.5 w-3.5" />
                    Выбран
                  </span>
                </div>
              </button>
            </div>
          </div>

          <aside class="p-6">
            <div class="rounded-[1.5rem] border border-amber-200/10 bg-white/5 p-4">
              <div class="flex items-center gap-3">
                <div class="rounded-2xl border border-amber-200/10 bg-white/5 p-2.5 text-amber-100">
                  <Swords class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Выбранный герой</p>
                  <p class="mt-1 text-sm text-slate-300">
                    {{ selectedCharacter ? selectedCharacter.name : 'Герой не выбран' }}
                  </p>
                </div>
              </div>

              <div
                v-if="selectedCharacter"
                class="mt-4 rounded-2xl border border-amber-200/10 bg-slate-950/40 px-4 py-3 text-sm leading-6 text-slate-300"
              >
                {{ selectedCharacter.race_name || resolveRaceLabel(selectedCharacter.race) }}
                <template v-if="selectedCharacter.subrace_name"> · {{ selectedCharacter.subrace_name }}</template>
                · {{ selectedCharacter.character_class_name || resolveCharacterClassLabel(selectedCharacter.character_class) }}
                · ур. {{ selectedCharacter.level }}
              </div>
            </div>

            <div class="mt-4 flex flex-col gap-3">
              <button
                :disabled="pending || selectedCharacterId === null || availableCharacters.length === 0"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-300/15 disabled:cursor-not-allowed disabled:opacity-60"
                type="button"
                @click="handleAccept"
              >
                {{ pending ? 'Входим в игру...' : 'Войти в игру этим героем' }}
              </button>

              <button
                class="inline-flex items-center justify-center gap-2 rounded-full border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-200 transition hover:border-amber-200/30"
                type="button"
                @click="emit('close')"
              >
                Отмена
              </button>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </teleport>
</template>

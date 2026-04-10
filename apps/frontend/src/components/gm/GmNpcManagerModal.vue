<script setup lang="ts">
import { Plus, Trash2, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { deleteGameActor, fetchGameActors } from '@/services/actorApi';
import PlayerCharacterCreateModal from '@/components/player/PlayerCharacterCreateModal.vue';
import type { GameActor } from '@/types/actor';
import { resolveCharacterClassLabel, resolveRaceLabel } from '@/utils/catalogLabel';

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  close: [];
}>();

const actors = ref<GameActor[]>([]);
const modalError = ref('');
const isLoading = ref(false);
const isSaving = ref(false);
const isConstructorOpen = ref(false);

/**
 * Загружает все данные менеджера NPC.
 */
async function loadModalData(): Promise<void> {
  isLoading.value = true;
  modalError.value = '';

  try {
    const nextActors = await fetchGameActors();
    actors.value = nextActors.filter((actor) => actor.kind === 'npc');
  } catch (error) {
    modalError.value = (error as Error).message;
  } finally {
    isLoading.value = false;
  }
}

/**
 * Обрабатывает создание NPC через общий конструктор.
 */
function handleActorCreated(actor: GameActor): void {
  actors.value = [...actors.value, actor].sort((left, right) => left.name.localeCompare(right.name, 'ru'));
  isConstructorOpen.value = false;
}

/**
 * Удаляет NPC из игры.
 */
async function handleDeleteActor(actorId: number): Promise<void> {
  isSaving.value = true;
  modalError.value = '';

  try {
    await deleteGameActor(actorId);
    actors.value = actors.value.filter((actor) => actor.id !== actorId);
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

    await loadModalData();
  },
);
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-6 backdrop-blur-sm"
    >
      <PlayerCharacterCreateModal
        :open="isConstructorOpen"
        mode="npc"
        @actor-created="handleActorCreated"
        @close="isConstructorOpen = false"
      />

      <div class="absolute inset-0" @click="emit('close')" />

      <section class="relative z-10 flex h-[min(52rem,calc(100vh-3rem))] w-full max-w-3xl flex-col overflow-hidden rounded-[2rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(17,24,39,0.98),rgba(2,6,23,0.98))] shadow-[0_30px_80px_rgba(2,6,23,0.65)]">
        <div class="flex items-start justify-between gap-4 border-b border-amber-200/10 p-6">
          <div>
            <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">
              NPC
            </p>
            <h2 class="mt-2 font-display text-3xl text-amber-50">
              Библиотека NPC
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

        <div class="border-b border-amber-200/10 p-6">
          <button
            class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-50 transition hover:border-amber-200/40 hover:bg-amber-300/15"
            :disabled="isSaving || isLoading"
            type="button"
            @click="isConstructorOpen = true"
          >
            <Plus class="h-4 w-4" />
            Создать NPC
          </button>
        </div>

        <div
          v-if="modalError"
          class="mx-6 mt-6 rounded-[1.25rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-100"
        >
          {{ modalError }}
        </div>

        <div
          v-if="isLoading"
          class="p-6 text-sm text-slate-300"
        >
          Загружаем NPC...
        </div>

        <div
          v-else
          class="flex-1 space-y-3 overflow-y-auto p-6"
        >
          <article
            v-for="actor in actors"
            :key="actor.id"
            class="flex items-center gap-4 rounded-[1.35rem] border border-amber-200/10 bg-white/5 p-4"
          >
            <div>
              <img
                v-if="actor.image_url"
                :src="actor.image_url"
                :alt="actor.name"
                class="h-20 w-16 rounded-xl border border-white/10 object-cover"
              >
              <span
                v-else
                class="flex h-20 w-16 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-lg font-semibold text-amber-100"
              >
                {{ actor.name.slice(0, 1) }}
              </span>
            </div>

            <div class="min-w-0 flex-1">
              <p class="truncate text-base text-amber-50">{{ actor.name }}</p>
              <p class="mt-1 truncate text-sm text-slate-300">
                {{ resolveRaceLabel(actor.race) }} · {{ resolveCharacterClassLabel(actor.character_class) }}
              </p>
              <p class="mt-1 text-xs text-slate-400">
                Ур. {{ actor.level }} · HP {{ actor.base_health ?? actor.health_max ?? 0 }} · {{ actor.movement_speed }} кл.
              </p>
            </div>

            <div class="flex shrink-0 gap-2">
              <button
                class="inline-flex items-center gap-2 rounded-full border border-rose-300/15 bg-rose-500/10 px-4 py-2 text-sm text-rose-200 transition hover:bg-rose-500/20"
                :disabled="isSaving"
                type="button"
                @click="handleDeleteActor(actor.id)"
              >
                <Trash2 class="h-4 w-4" />
                Удалить
              </button>
            </div>
          </article>

          <div
            v-if="actors.length === 0"
            class="rounded-[1.35rem] border border-amber-200/10 bg-white/5 px-4 py-4 text-sm text-slate-300"
          >
            NPC пока не созданы.
          </div>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<script lang="ts" setup>
import {CalendarClock, Shield, Users} from 'lucide-vue-next';
import {RouterLink} from 'vue-router';
import GmGameStatusTabs from '@/components/gm/GmGameStatusTabs.vue';
import type {GameStatusFilter, GameSummary} from '@/types/game';
import {formatGameStatus} from '@/utils/gameStatus';

defineProps<{
  activeStatusFilter: GameStatusFilter;
  games: GameSummary[];
  loading: boolean;
}>();

const emit = defineEmits<{
  'update:statusFilter': [value: GameStatusFilter];
}>();

/**
 * Преобразует дату в локальную короткую строку.
 */
function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
}

</script>

<template>
  <section class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div class="space-y-1">
        <p class="text-xs uppercase text-amber-200/50">
          Мои игры
        </p>
        <h2 class="font-display text-2xl text-amber-50">
          Список игровых столов
        </h2>
        <p class="text-sm leading-6 text-slate-300">
          Все кампании, которыми ты управляешь как мастер.
        </p>
      </div>
      <div class="space-y-3">
        <p class="text-sm text-slate-400">
          {{ loading ? 'Обновляем список...' : `Всего игр: ${games.length}` }}
        </p>
        <GmGameStatusTabs
            :model-value="activeStatusFilter"
            @update:model-value="emit('update:statusFilter', $event)"
        />
      </div>
    </div>

    <div
        v-if="games.length === 0"
        class="mt-5 rounded-[1.4rem] border border-dashed border-amber-200/15 bg-slate-950/20 px-5 py-8 text-sm text-slate-300"
    >
      Пока игр нет. Создай первую кампанию в форме выше.
    </div>

    <div
        v-else
        class="mt-5 grid gap-4 lg:grid-cols-2"
    >
      <RouterLink
          v-for="game in games"
          :key="game.id"
          :to="`/cabinet/gm/games/${game.id}`"
          class="block rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-5 transition hover:border-amber-200/20 hover:bg-slate-950/40"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="space-y-2">
            <p class="text-xs uppercase text-amber-200/50">
              Игра #{{ game.id }}
            </p>
            <h3 class="font-display text-[1.55rem] text-amber-50">
              {{ game.title }}
            </h3>
          </div>

          <span
              class="rounded-full border border-amber-300/20 bg-amber-300/10 px-3 py-1 text-xs uppercase text-amber-100">
            {{ formatGameStatus(game.status) }}
          </span>
        </div>

        <p class="mt-3 min-h-[3rem] text-sm leading-6 text-slate-300">
          {{ game.description || 'Описание пока не заполнено.' }}
        </p>

        <div class="mt-5 grid gap-3 text-sm text-slate-300 sm:grid-cols-3">
          <div class="rounded-2xl border border-amber-200/10 bg-white/5 px-3 py-3">
            <Shield class="mb-2 h-4 w-4 text-amber-200"/>
            <p class="text-xs uppercase text-amber-200/45">
              Мастер
            </p>
            <p class="mt-1 text-sm text-amber-50">
              {{ game.gm.name }}
            </p>
          </div>

          <div class="rounded-2xl border border-amber-200/10 bg-white/5 px-3 py-3">
            <Users class="mb-2 h-4 w-4 text-amber-200"/>
            <p class="text-xs uppercase text-amber-200/45">
              Участники
            </p>
            <p class="mt-1 text-sm text-amber-50">
              {{ game.members_count }}
            </p>
          </div>

          <div class="rounded-2xl border border-amber-200/10 bg-white/5 px-3 py-3">
            <CalendarClock class="mb-2 h-4 w-4 text-amber-200"/>
            <p class="text-xs uppercase text-amber-200/45">
              Создана
            </p>
            <p class="mt-1 text-sm text-amber-50">
              {{ formatDate(game.created_at) }}
            </p>
          </div>
        </div>
      </RouterLink>
    </div>
  </section>
</template>

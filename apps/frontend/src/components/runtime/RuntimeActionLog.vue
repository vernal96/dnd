<script setup lang="ts">
import { computed } from 'vue';

type RuntimeActionLogEntry = {
  actor_name?: string;
  attack_roll?: number;
  attack_total?: number;
  damage?: number;
  damage_bonus?: number;
  damage_roll?: number;
  difficulty_class?: number;
  id?: string;
  is_failed?: boolean;
  is_hit?: boolean;
  item_name?: string;
  save_roll?: number;
  save_total?: number;
  target_actor_name?: string;
  target_armor_class?: number;
  type?: string;
};

const props = defineProps<{
  entries: Array<Record<string, unknown>>;
}>();

const normalizedEntries = computed<RuntimeActionLogEntry[]>(() =>
  props.entries
    .filter((entry) => entry.type === 'weapon_attack' || entry.type === 'trip_attack')
    .map((entry) => ({
      actor_name: typeof entry.actor_name === 'string' ? entry.actor_name : undefined,
      attack_roll: typeof entry.attack_roll === 'number' ? entry.attack_roll : undefined,
      attack_total: typeof entry.attack_total === 'number' ? entry.attack_total : undefined,
      damage: typeof entry.damage === 'number' ? entry.damage : undefined,
      damage_bonus: typeof entry.damage_bonus === 'number' ? entry.damage_bonus : undefined,
      damage_roll: typeof entry.damage_roll === 'number' ? entry.damage_roll : undefined,
      difficulty_class: typeof entry.difficulty_class === 'number' ? entry.difficulty_class : undefined,
      id: typeof entry.id === 'string' ? entry.id : undefined,
      is_failed: typeof entry.is_failed === 'boolean' ? entry.is_failed : undefined,
      is_hit: typeof entry.is_hit === 'boolean' ? entry.is_hit : undefined,
      item_name: typeof entry.item_name === 'string' ? entry.item_name : undefined,
      save_roll: typeof entry.save_roll === 'number' ? entry.save_roll : undefined,
      save_total: typeof entry.save_total === 'number' ? entry.save_total : undefined,
      target_actor_name: typeof entry.target_actor_name === 'string' ? entry.target_actor_name : undefined,
      target_armor_class: typeof entry.target_armor_class === 'number' ? entry.target_armor_class : undefined,
      type: typeof entry.type === 'string' ? entry.type : undefined,
    }))
    .slice()
    .reverse()
    .slice(0, 8),
);
</script>

<template>
  <section class="rounded-[1.5rem] border border-amber-200/10 bg-slate-950/30 p-4">
    <div class="flex items-center justify-between gap-3">
      <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Журнал действий</p>
      <span class="rounded-full border border-amber-200/10 bg-white/5 px-2.5 py-1 text-[0.65rem] uppercase text-slate-400">
        {{ normalizedEntries.length }}
      </span>
    </div>

    <div class="mt-4 grid gap-2">
      <article
        v-for="entry in normalizedEntries"
        :key="entry.id ?? `${entry.actor_name}-${entry.target_actor_name}-${entry.attack_total}`"
        class="rounded-2xl border px-3 py-3"
        :class="entry.is_hit || entry.is_failed ? 'border-red-200/15 bg-red-950/20' : 'border-slate-200/10 bg-white/5'"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate text-sm text-amber-50">
              <template v-if="entry.type === 'trip_attack'">
                {{ entry.actor_name ?? 'Актор' }} сбивает с ног {{ entry.target_actor_name ?? 'цель' }}
              </template>
              <template v-else>
                {{ entry.actor_name ?? 'Актор' }} атакует {{ entry.target_actor_name ?? 'цель' }}
              </template>
            </p>
            <p
              v-if="entry.type === 'weapon_attack'"
              class="mt-1 truncate text-xs text-slate-400"
            >
              {{ entry.item_name ?? 'Оружие' }} · бросок {{ entry.attack_roll ?? '—' }} / итог {{ entry.attack_total ?? '—' }} против КД {{ entry.target_armor_class ?? '—' }}
            </p>
            <p
              v-else
              class="mt-1 truncate text-xs text-slate-400"
            >
              Спасбросок Силы {{ entry.save_roll ?? '—' }} / итог {{ entry.save_total ?? '—' }} против СЛ {{ entry.difficulty_class ?? '—' }}
            </p>
          </div>

          <span
            class="shrink-0 rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase"
            :class="entry.is_hit || entry.is_failed ? 'bg-red-400/15 text-red-100' : 'bg-slate-300/10 text-slate-300'"
          >
            <template v-if="entry.type === 'trip_attack'">{{ entry.is_failed ? 'Упал' : 'Устоял' }}</template>
            <template v-else>{{ entry.is_hit ? 'Попал' : 'Промах' }}</template>
          </span>
        </div>

        <p
          v-if="entry.type === 'weapon_attack' && entry.is_hit"
          class="mt-2 text-xs text-red-100/90"
        >
          Урон {{ entry.damage ?? 0 }} = кубик {{ entry.damage_roll ?? 0 }} + бонус {{ entry.damage_bonus ?? 0 }}
        </p>
        <p
          v-if="entry.type === 'trip_attack' && entry.is_failed"
          class="mt-2 text-xs text-red-100/90"
        >
          На цель наложен эффект «Упал».
        </p>
      </article>

      <p
        v-if="normalizedEntries.length === 0"
        class="rounded-2xl border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-400"
      >
        Действий пока нет.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { CatalogItem } from '@/types/item';
import type { RuntimeActorInstance } from '@/types/runtimeScene';

type EquippedWeapon = {
  item: CatalogItem;
  slot: string;
};

const props = defineProps<{
  actor: RuntimeActorInstance;
  catalog: CatalogItem[];
  pending: boolean;
  target: RuntimeActorInstance;
}>();

const emit = defineEmits<{
  close: [];
  tripAttack: [];
  weaponAttack: [slot: string];
}>();

const slotLabels: Record<string, string> = {
  main_hand: 'Основная рука',
  off_hand: 'Вторая рука',
  ranged: 'Дальний бой',
};

const equippedWeapons = computed<EquippedWeapon[]>(() => {
  const inventory = props.actor.runtime_state?.inventory ?? [];
  const weaponSlots = ['main_hand', 'off_hand', 'ranged'];

  return weaponSlots.flatMap((slot) => {
    const inventoryItem = inventory.find((entry) => entry.slot === slot);
    const catalogItem = props.catalog.find((item) => item.code === inventoryItem?.itemCode);

    if (catalogItem?.type !== 'melee-weapon' && catalogItem?.type !== 'ranged-weapon') {
      return [];
    }

    return [{ item: catalogItem, slot }];
  });
});
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/45 p-4 backdrop-blur-sm sm:items-center" @click.self="emit('close')">
    <div class="w-full max-w-4xl rounded-[1.5rem] border border-amber-200/15 bg-slate-950/95 p-4 shadow-[0_28px_90px_rgba(2,6,23,0.75)]">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-amber-200/50">Действие</p>
          <h2 class="mt-1 font-display text-2xl text-amber-50">{{ actor.name }} → {{ target.name }}</h2>
        </div>
        <button class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase text-slate-300 transition hover:border-white/20 hover:text-white" type="button" @click="emit('close')">
          Закрыть
        </button>
      </div>

      <div class="mt-5 grid gap-3 md:grid-cols-3">
        <section class="rounded-[1.15rem] border border-red-300/15 bg-red-950/20 p-3">
          <p class="text-xs uppercase tracking-[0.18em] text-red-100/60">1. Оружие</p>
          <div class="mt-3 grid gap-2">
            <button
              v-for="weapon in equippedWeapons"
              :key="weapon.slot"
              class="flex items-center gap-3 rounded-2xl border border-red-200/15 bg-white/[0.04] p-3 text-left transition hover:border-red-200/35 disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="pending"
              type="button"
              @click="emit('weaponAttack', weapon.slot)"
            >
              <img
                v-if="weapon.item.image_url"
                :src="weapon.item.image_url"
                :alt="weapon.item.name"
                class="h-10 w-10 rounded-xl border border-white/10 object-cover"
              >
              <span v-else class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-sm font-semibold text-red-100">
                {{ weapon.item.name.slice(0, 1) }}
              </span>
              <span class="min-w-0">
                <span class="block truncate text-sm text-red-50">{{ weapon.item.name }}</span>
                <span class="block text-xs text-red-100/55">{{ slotLabels[weapon.slot] }} · {{ weapon.item.damageDice ?? '—' }}</span>
              </span>
            </button>

            <p v-if="equippedWeapons.length === 0" class="rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-4 text-sm text-slate-400">
              Нет экипированного оружия.
            </p>
          </div>
        </section>

        <section class="rounded-[1.15rem] border border-amber-200/10 bg-white/[0.03] p-3">
          <p class="text-xs uppercase tracking-[0.18em] text-amber-200/50">2. Скилы</p>
          <button
            class="mt-3 w-full rounded-2xl border border-orange-200/15 bg-orange-500/5 px-3 py-3 text-left text-sm text-orange-100 transition hover:border-orange-200/30 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="pending"
            type="button"
            @click="emit('tripAttack')"
          >
            Сбить с ног
            <span class="mt-1 block text-xs text-orange-100/55">Спасбросок Силы цели, провал накладывает «Упал».</span>
          </button>
        </section>

        <section class="rounded-[1.15rem] border border-amber-200/10 bg-white/[0.03] p-3 opacity-75">
          <p class="text-xs uppercase tracking-[0.18em] text-amber-200/50">3. Предметы</p>
          <p class="mt-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-4 text-sm text-slate-400">
            Usable items будут подключены отдельными typed runtime-действиями.
          </p>
        </section>
      </div>
    </div>
  </div>
</template>

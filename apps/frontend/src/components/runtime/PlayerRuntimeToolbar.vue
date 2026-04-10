<script setup lang="ts">
import { Heart } from 'lucide-vue-next';
import { computed } from 'vue';
import type { CatalogItem } from '@/types/item';
import type { RuntimeActorInstance, RuntimeActorInventoryItem } from '@/types/runtimeScene';
import { resolveCharacterClassLabel, resolveRaceLabel } from '@/utils/catalogLabel';

type InventorySlot = {
  item: RuntimeActorInventoryItem | null;
  key: string;
  meta: {
    imageUrl: string | null;
    name: string;
  } | null;
};

const props = defineProps<{
  actor: RuntimeActorInstance | null;
  catalog: CatalogItem[];
  items: RuntimeActorInventoryItem[];
}>();

const emit = defineEmits<{
  openInventory: [actorId: number];
}>();

const INVENTORY_COLUMNS = 30;
const SLOT_ROWS = 2;

const portraitImageUrl = computed(() => props.actor?.image_url ?? props.actor?.runtime_state?.image_url ?? null);
const actorLevel = computed(() => props.actor?.runtime_state?.level ?? null);
const actorRace = computed(() => resolveRaceLabel(props.actor?.runtime_state?.race));
const actorClass = computed(() => resolveCharacterClassLabel(props.actor?.runtime_state?.character_class));
const actorHpCurrent = computed(() => props.actor?.hp_current ?? null);
const actorHpMax = computed(() => props.actor?.hp_max ?? null);

const inventorySlots = computed<InventorySlot[]>(() => {
  const totalSlots = INVENTORY_COLUMNS * SLOT_ROWS;

  return Array.from({ length: totalSlots }, (_, index) => {
    const item = props.items[index] ?? null;

    if (item === null) {
      return {
        item: null,
        key: `empty-${index}`,
        meta: null,
      };
    }

    const catalogItem = props.catalog.find((entry) => entry.code === item.itemCode) ?? null;

    return {
      item,
      key: `${item.itemCode}-${item.slot ?? 'bag'}-${item.isEquipped ? 'eq' : 'bag'}-${index}`,
      meta: {
        imageUrl: catalogItem?.image_url ?? null,
        name: catalogItem?.name ?? prettifyItemCode(item.itemCode),
      },
    };
  });
});

function prettifyItemCode(itemCode: string): string {
  return itemCode
    .split('-')
    .filter((part) => part !== '')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function resolveInitial(name: string | null | undefined): string {
  if (!name) {
    return '?';
  }

  return name.slice(0, 1).toUpperCase();
}

function handleOpenInventory(): void {
  if (props.actor === null) {
    return;
  }

  emit('openInventory', props.actor.id);
}
</script>

<template>
  <div
    v-if="actor"
    class="pointer-events-none fixed bottom-4 left-4 right-4 z-30"
  >
    <div class="player-runtime-toolbar__content flex items-end gap-4">
      <div class="pointer-events-auto group relative">
        <button
          class="toolbar-portrait relative block h-56 w-56 overflow-visible rounded-[1rem] border border-amber-200/25 bg-slate-950/80 shadow-[0_24px_60px_rgba(2,6,23,0.45)]"
          type="button"
          @click="handleOpenInventory"
        >
          <div class="absolute inset-[0.35rem] overflow-hidden rounded-[0.8rem] border border-white/10 bg-slate-900/75">
            <img
              v-if="portraitImageUrl"
              :src="portraitImageUrl"
              :alt="actor.name"
              class="h-full w-full object-cover object-[center_top]"
            >
            <div
              v-else
              class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top,rgba(251,191,36,0.3),transparent_72%),linear-gradient(180deg,rgba(30,41,59,0.92),rgba(15,23,42,0.96))] font-display text-4xl text-amber-50"
            >
              {{ resolveInitial(actor.name) }}
            </div>
          </div>

          <div class="absolute inset-x-[0.35rem] bottom-[0.35rem] rounded-[1rem] border border-black/10 bg-[linear-gradient(180deg,rgba(15,23,42,0.2),rgba(15,23,42,0.92))] px-2.5 py-2 backdrop-blur-md">
            <div class="flex items-end justify-between gap-2">
              <span class="flex h-7 w-7 items-center justify-center rounded-full border border-amber-200/30 bg-slate-950/90 text-xs font-semibold text-amber-50">
                {{ actorLevel ?? '—' }}
              </span>

              <span class="flex min-w-0 items-center gap-1.5 rounded-full bg-slate-950/55 px-2 py-1 text-[0.72rem] font-medium text-amber-50">
                <Heart class="h-3.5 w-3.5 shrink-0 text-rose-300" />
                <span class="truncate">{{ actorHpCurrent ?? '—' }}/{{ actorHpMax ?? '—' }}</span>
              </span>
            </div>
          </div>
        </button>

        <div class="pointer-events-none absolute bottom-full left-0 mb-3 w-56 rounded-[1.25rem] border border-amber-200/15 bg-slate-950/95 px-4 py-3 text-left opacity-0 shadow-[0_24px_60px_rgba(2,6,23,0.5)] transition duration-150 group-hover:opacity-100">
          <p class="font-display text-xl text-amber-50">{{ actor.name }}</p>
          <p class="mt-2 text-sm text-slate-300">{{ actorRace }}</p>
          <p class="mt-1 text-sm text-slate-300">{{ actorClass }}</p>
        </div>
      </div>

      <div class="pointer-events-auto flex-1 rounded-[1.8rem] border border-amber-200/15 bg-[linear-gradient(180deg,rgba(15,23,42,0.8),rgba(2,6,23,0.92))] p-3 shadow-[0_24px_60px_rgba(2,6,23,0.42)] backdrop-blur-md">
        <div
          class="grid"
          :style="{
            columnGap: '0.5rem',
            gridTemplateColumns: `repeat(${INVENTORY_COLUMNS}, minmax(0, 1fr))`,
            rowGap: '0.5rem',
          }"
        >
          <button
            v-for="slot in inventorySlots"
            :key="slot.key"
            class="group/slot relative aspect-square flex items-center justify-center overflow-hidden rounded-[0.95rem] border transition"
            :class="slot.item ? 'border-amber-200/20 bg-white/[0.06] hover:border-amber-200/35' : 'border-white/10 bg-white/[0.04]'"
            type="button"
            @click="handleOpenInventory"
          >
            <img
              v-if="slot.meta?.imageUrl"
              :src="slot.meta.imageUrl"
              :alt="slot.meta.name"
              class="h-full w-full object-cover"
            >
            <span
              v-else-if="slot.item"
              class="font-display text-base text-amber-50"
            >
              {{ resolveInitial(slot.meta?.name) }}
            </span>

            <span
              v-if="slot.item"
              class="absolute bottom-1 right-1 rounded-full bg-slate-950/85 px-1.5 py-0.5 text-[0.62rem] font-semibold leading-none text-amber-50"
            >
              {{ slot.item.quantity }}
            </span>

            <span
              v-if="slot.meta"
              class="pointer-events-none absolute inset-x-1 bottom-1 rounded-md bg-slate-950/85 px-1.5 py-1 text-[0.62rem] leading-none text-slate-100 opacity-0 transition group-hover/slot:opacity-100"
            >
              {{ slot.meta.name }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.toolbar-portrait {
  backdrop-filter: blur(16px);
}

@media (max-width: 960px) {
  .player-runtime-toolbar__content {
    flex-direction: column;
    align-items: stretch;
  }

  .toolbar-portrait {
    width: 8rem;
    height: 8rem;
  }
}
</style>

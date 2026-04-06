<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { CatalogItem } from '@/types/item';
import type { RuntimeActorInventoryItem } from '@/types/runtimeScene';

const props = defineProps<{
  actorName: string;
  catalog: CatalogItem[];
  items: RuntimeActorInventoryItem[];
  open: boolean;
}>();

const emit = defineEmits<{
  close: [];
}>();

const hoveredItemKey = ref<string | null>(null);

const normalizedItems = computed(() =>
  props.items.map((item, index) => {
    const catalogItem = props.catalog.find((entry) => entry.code === item.itemCode) ?? null;

    return {
      ...item,
      description: catalogItem?.description ?? null,
      imageUrl: catalogItem?.image_url ?? null,
      key: `${item.itemCode}-${item.slot ?? 'bag'}-${item.isEquipped ? 'eq' : 'bag'}-${index}`,
      name: catalogItem?.name ?? prettifyItemCode(item.itemCode),
    };
  }),
);

function prettifyItemCode(itemCode: string): string {
  return itemCode
    .split('-')
    .filter((part) => part !== '')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function resolveItemGlyph(name: string): string {
  return name.slice(0, 1).toUpperCase();
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/70 p-6 backdrop-blur-sm"
    >
      <div class="absolute inset-0" @click="emit('close')" />

      <section class="relative z-10 flex max-h-[min(44rem,calc(100vh-3rem))] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(17,24,39,0.98),rgba(2,6,23,0.98))] shadow-[0_30px_80px_rgba(2,6,23,0.65)]">
        <div class="flex items-start justify-between gap-4 border-b border-amber-200/10 p-6">
          <div>
            <p class="text-xs uppercase tracking-[0.2em] text-amber-200/50">Инвентарь</p>
            <h2 class="mt-2 font-display text-3xl text-amber-50">{{ actorName }}</h2>
          </div>

          <button
            class="rounded-full border border-amber-200/10 bg-white/5 p-2 text-slate-300 transition hover:border-amber-200/30 hover:text-amber-50"
            type="button"
            @click="emit('close')"
          >
            <X class="h-4 w-4" />
          </button>
        </div>

        <div class="relative flex-1 overflow-y-auto p-6">
          <div
            v-if="normalizedItems.length === 0"
            class="rounded-[1.35rem] border border-amber-200/10 bg-white/5 px-4 py-4 text-sm text-slate-300"
          >
            Инвентарь пуст.
          </div>

          <div
            v-else
            class="grid grid-cols-4 gap-3 md:grid-cols-5 xl:grid-cols-6"
          >
            <div
              v-for="item in normalizedItems"
              :key="item.key"
              class="group relative"
              @mouseenter="hoveredItemKey = item.key"
              @mouseleave="hoveredItemKey = null"
            >
              <div class="flex aspect-square flex-col overflow-hidden rounded-[1.35rem] border border-amber-200/10 bg-white/5">
                <div class="relative flex-1 overflow-hidden bg-slate-950/40">
                  <img
                    v-if="item.imageUrl"
                    :src="item.imageUrl"
                    :alt="item.name"
                    class="h-full w-full object-cover"
                  >
                  <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top,rgba(245,158,11,0.22),transparent_70%)] text-2xl text-amber-100"
                  >
                    {{ resolveItemGlyph(item.name) }}
                  </div>

                  <span class="absolute right-2 top-2 rounded-full border border-amber-200/10 bg-slate-950/85 px-2 py-1 text-[0.65rem] font-semibold text-amber-50">
                    x{{ item.quantity }}
                  </span>
                </div>

                <div class="border-t border-amber-200/10 px-2 py-2">
                  <p class="truncate text-xs text-amber-50">{{ item.name }}</p>
                </div>
              </div>

              <div
                v-if="hoveredItemKey === item.key"
                class="pointer-events-none absolute left-1/2 top-full z-10 mt-3 w-56 -translate-x-1/2 rounded-[1.2rem] border border-amber-200/10 bg-slate-950/95 px-4 py-3 shadow-[0_18px_50px_rgba(2,6,23,0.55)]"
              >
                <p class="text-sm text-amber-50">{{ item.name }}</p>
                <p class="mt-1 text-xs uppercase tracking-[0.16em] text-amber-200/50">{{ item.itemCode }}</p>
                <p class="mt-2 text-sm leading-6 text-slate-300">
                  {{ item.description || 'Описание предмета пока не заполнено.' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </Teleport>
</template>

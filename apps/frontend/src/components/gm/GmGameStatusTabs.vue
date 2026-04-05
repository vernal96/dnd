<script lang="ts" setup>
import type {GameStatusFilter} from '@/types/game';

defineProps<{
  modelValue: GameStatusFilter;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: GameStatusFilter];
}>();

const options: Array<{ label: string; value: GameStatusFilter }> = [
  {label: 'Все', value: 'all'},
  {label: 'Черновики', value: 'draft'},
  {label: 'Активные', value: 'active'},
  {label: 'Пауза', value: 'paused'},
  {label: 'Завершённые', value: 'completed'},
];
</script>

<template>
  <div class="flex flex-wrap gap-2">
    <button
        v-for="option in options"
        :key="option.value"
        :class="
        modelValue === option.value
          ? 'border-amber-300/25 bg-amber-300/10 text-amber-50'
          : 'border-amber-100/10 bg-white/5 text-slate-300'
      "
        class="rounded-full border px-4 py-2 text-sm transition hover:border-amber-200/20 hover:text-amber-50"
        type="button"
        @click="emit('update:modelValue', option.value)"
    >
      {{ option.label }}
    </button>
  </div>
</template>

<script setup lang="ts">
import type { AuthMode } from '@/types/auth';

defineProps<{
  modelValue: AuthMode;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: AuthMode];
}>();

const options: Array<{ label: string; value: AuthMode }> = [
  { label: 'Вход', value: 'login' },
  { label: 'Регистрация', value: 'register' },
];
</script>

<template>
  <div class="auth-shell grid grid-cols-2 gap-2 p-1.5">
    <button
      v-for="option in options"
      :key="option.value"
      :class="
        modelValue === option.value
          ? 'border border-amber-300/25 bg-[linear-gradient(180deg,rgba(249,216,160,0.18),rgba(255,255,255,0.06))] text-amber-50 shadow-[0_10px_30px_rgba(18,10,25,0.28)]'
          : 'border border-transparent text-slate-300/90 hover:bg-white/5 hover:text-amber-50/90'
      "
      class="rounded-2xl px-4 py-3 text-sm font-medium transition duration-200"
      type="button"
      @click="emit('update:modelValue', option.value)"
    >
      {{ option.label }}
    </button>
  </div>
</template>

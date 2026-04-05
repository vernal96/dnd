<script lang="ts" setup>
import type {Component} from 'vue';

defineProps<{
  autocomplete?: string;
  error?: string;
  icon?: Component;
  label: string;
  modelValue: string;
  name: string;
  placeholder: string;
  type?: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();
</script>

<template>
  <label class="block space-y-2">
    <span class="text-sm font-medium text-amber-50/90">{{ label }}</span>

    <div class="form-control-shell">
      <component
          :is="icon"
          v-if="icon"
          class="h-[18px] w-[18px] flex-none text-amber-200/70"
      />

      <input
          :autocomplete="autocomplete"
          :name="name"
          :placeholder="placeholder"
          :type="type ?? 'text'"
          :value="modelValue"
          class="form-control"
          @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      />
    </div>

    <p
        v-if="error"
        class="text-xs text-rose-300"
    >
      {{ error }}
    </p>
  </label>
</template>

<script setup lang="ts">
defineProps<{
  error?: string;
  label: string;
  modelValue: string;
  name: string;
  placeholder: string;
  rows?: number;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();
</script>

<template>
  <label class="block space-y-2">
    <span class="text-sm font-medium text-amber-50/90">{{ label }}</span>

    <div class="form-control-shell items-start">
      <textarea
        :name="name"
        :placeholder="placeholder"
        :rows="rows ?? 4"
        :value="modelValue"
        class="form-control form-textarea"
        @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
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

<script setup lang="ts">
import { ScrollText, Sparkles } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import FormTextInput from '@/components/form/FormTextInput.vue';
import FormTextarea from '@/components/form/FormTextarea.vue';
import type { CreateGamePayload } from '@/types/game';

const props = defineProps<{
  pending: boolean;
}>();

const emit = defineEmits<{
  submit: [payload: CreateGamePayload];
}>();

const title = ref('');
const description = ref('');
const localError = ref('');

const isDisabled = computed<boolean>(() => title.value.trim().length < 3 || props.pending);

/**
 * Валидирует форму и отправляет новую игру наружу.
 */
function submitForm(): void {
  localError.value = '';

  if (title.value.trim().length < 3) {
    localError.value = 'Название игры должно содержать минимум 3 символа.';

    return;
  }

  emit('submit', {
    title: title.value.trim(),
    description: description.value.trim(),
  });

  title.value = '';
  description.value = '';
}
</script>

<template>
  <form
    class="rounded-[1.75rem] border border-amber-200/10 bg-white/5 p-5"
    @submit.prevent="submitForm"
  >
    <div class="flex items-start gap-3">
      <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-amber-200/10 bg-amber-300/10">
        <Sparkles class="h-5 w-5 text-amber-200" />
      </div>

      <div class="space-y-1">
        <p class="text-xs uppercase text-amber-200/50">
          Новая игра
        </p>
        <h2 class="font-display text-2xl text-amber-50">
          Создать кампанию
        </h2>
        <p class="max-w-xl text-sm leading-6 text-slate-300">
          Создай новый игровой стол, чтобы начать подготовку сцены и пригласить участников.
        </p>
      </div>
    </div>

    <div class="mt-5 space-y-4">
      <FormTextInput
        v-model="title"
        autocomplete="off"
        label="Название игры"
        name="title"
        placeholder="Например, Тайна янтарного шпиля"
      />

      <FormTextarea
        v-model="description"
        label="Краткое описание"
        name="description"
        placeholder="О чем игра, какой тон, какая отправная точка у партии."
        :rows="4"
      />
    </div>

    <p
      v-if="localError"
      class="mt-4 rounded-2xl border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
    >
      {{ localError }}
    </p>

    <div class="mt-5">
      <button
        class="cta-primary"
        :disabled="isDisabled"
        type="submit"
      >
        <ScrollText class="h-4 w-4" />
        {{ pending ? 'Создаем игру...' : 'Создать игру' }}
      </button>
    </div>
  </form>
</template>

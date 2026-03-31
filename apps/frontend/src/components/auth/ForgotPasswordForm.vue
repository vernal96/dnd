<script setup lang="ts">
import { Mail, ScrollText } from 'lucide-vue-next';
import { ref } from 'vue';
import FormTextInput from '@/components/form/FormTextInput.vue';
import type { ForgotPasswordPayload } from '@/types/auth';

defineProps<{
  pending: boolean;
}>();

const emit = defineEmits<{
  back: [];
  submit: [payload: ForgotPasswordPayload];
}>();

const email = ref('');
const localError = ref('');

/**
 * Валидирует форму и отправляет запрос на восстановление пароля.
 */
function submitForm(): void {
  localError.value = '';

  if (email.value.trim() === '') {
    localError.value = 'Укажи email, чтобы получить инструкции по восстановлению.';

    return;
  }

  emit('submit', {
    email: email.value.trim(),
  });
}
</script>

<template>
  <form
    class="space-y-3.5"
    @submit.prevent="submitForm"
  >
    <div class="space-y-1.5">
      <h2 class="font-display text-[1.34rem] text-amber-50">
        Восстановление доступа
      </h2>
      <p class="text-sm leading-5 text-slate-300">
        Введи email учетной записи, чтобы получить инструкции для смены пароля.
      </p>
    </div>

    <FormTextInput
      v-model="email"
      autocomplete="email"
      label="Email"
      name="email"
      placeholder="login@table.quest"
      :icon="Mail"
    />

    <p
      v-if="localError"
      class="rounded-2xl border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
    >
      {{ localError }}
    </p>

    <div class="space-y-2.5">
      <button
        class="cta-primary w-full"
        :disabled="pending"
        type="submit"
      >
        <ScrollText class="h-4 w-4" />
        {{ pending ? 'Отправляем письмо...' : 'Отправить инструкции' }}
      </button>

      <button
        class="cta-secondary w-full"
        type="button"
        @click="emit('back')"
      >
        Вернуться ко входу
      </button>
    </div>
  </form>
</template>

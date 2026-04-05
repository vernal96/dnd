<script lang="ts" setup>
import {KeyRound, ShieldCheck} from 'lucide-vue-next';
import {computed, ref} from 'vue';
import FormTextInput from '@/components/form/FormTextInput.vue';
import type {ResetPasswordPayload} from '@/types/auth';

const props = defineProps<{
  email: string;
  pending: boolean;
  token: string;
}>();

const emit = defineEmits<{
  back: [];
  submit: [payload: ResetPasswordPayload];
}>();

const password = ref('');
const passwordConfirmation = ref('');
const localError = ref('');

const isDisabled = computed<boolean>(() => {
  return password.value.trim().length < 8 || passwordConfirmation.value.trim().length < 8;
});

/**
 * Валидирует форму и отправляет новый пароль на backend.
 */
function submitForm(): void {
  localError.value = '';

  if (props.token.trim() === '' || props.email.trim() === '') {
    localError.value = 'Ссылка для сброса пароля неполная или повреждена.';

    return;
  }

  if (password.value.trim().length < 8) {
    localError.value = 'Пароль должен содержать минимум 8 символов.';

    return;
  }

  if (password.value !== passwordConfirmation.value) {
    localError.value = 'Подтверждение пароля не совпадает.';

    return;
  }

  emit('submit', {
    token: props.token,
    email: props.email,
    password: password.value,
    passwordConfirmation: passwordConfirmation.value,
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
        Новый пароль
      </h2>
      <p class="text-sm leading-5 text-slate-300">
        Задай новый пароль для аккаунта {{ email || 'пользователя' }}.
      </p>
    </div>

    <div class="rounded-[1.2rem] border border-amber-200/10 bg-white/5 px-4 py-3 text-sm text-slate-300">
      <span class="text-amber-100">Email:</span> {{ email }}
    </div>

    <FormTextInput
        v-model="password"
        :icon="KeyRound"
        autocomplete="new-password"
        label="Новый пароль"
        name="password"
        placeholder="Минимум 8 символов"
        type="password"
    />

    <FormTextInput
        v-model="passwordConfirmation"
        :icon="ShieldCheck"
        autocomplete="new-password"
        label="Подтверждение пароля"
        name="password_confirmation"
        placeholder="Повтори новый пароль"
        type="password"
    />

    <p
        v-if="localError"
        class="rounded-2xl border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
    >
      {{ localError }}
    </p>

    <div class="space-y-2.5">
      <button
          :disabled="pending || isDisabled"
          class="cta-primary w-full"
          type="submit"
      >
        {{ pending ? 'Обновляем пароль...' : 'Сохранить новый пароль' }}
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

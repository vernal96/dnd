<script setup lang="ts">
import { KeyRound, Mail, UserRound } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import FormTextInput from '@/components/form/FormTextInput.vue';
import type { RegisterPayload } from '@/types/auth';

defineProps<{
  pending: boolean;
}>();

const emit = defineEmits<{
  submit: [payload: RegisterPayload];
}>();

const login = ref('');
const email = ref('');
const password = ref('');
const localError = ref('');

const isDisabled = computed<boolean>(() => {
  return login.value.trim() === '' || email.value.trim() === '' || password.value.trim().length < 8;
});

/**
 * Валидирует поля формы и отправляет payload регистрации наружу.
 */
function submitForm(): void {
  localError.value = '';

  if (login.value.trim() === '' || email.value.trim() === '') {
    localError.value = 'Логин и email обязательны для регистрации.';

    return;
  }

  if (password.value.trim().length < 8) {
    localError.value = 'Пароль должен содержать минимум 8 символов.';

    return;
  }

  emit('submit', {
    email: email.value.trim(),
    login: login.value.trim(),
    password: password.value,
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
        Регистрация
      </h2>
      <p class="text-sm leading-5 text-slate-300">
        Создай учетную запись и получи доступ к личному кабинету.
      </p>
    </div>

    <FormTextInput
      v-model="login"
      autocomplete="username"
      label="Логин"
      name="login"
      placeholder="Например, alrik"
      :icon="UserRound"
    />

    <FormTextInput
      v-model="email"
      autocomplete="email"
      label="Email"
      name="email"
      placeholder="login@table.quest"
      :icon="Mail"
    />

    <FormTextInput
      v-model="password"
      autocomplete="new-password"
      label="Пароль"
      name="password"
      placeholder="Минимум 8 символов"
      type="password"
      :icon="KeyRound"
    />

    <p
      v-if="localError"
      class="rounded-2xl border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
    >
      {{ localError }}
    </p>

    <div>
      <button
        class="cta-primary w-full"
        :disabled="pending || isDisabled"
        type="submit"
      >
        {{ pending ? 'Создаем аккаунт...' : 'Создать аккаунт' }}
      </button>
    </div>
  </form>
</template>

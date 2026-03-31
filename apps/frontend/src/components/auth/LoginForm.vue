<script setup lang="ts">
import { KeyRound, Mail, ScrollText } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AuthField from '@/components/auth/AuthField.vue';
import type { LoginPayload } from '@/types/auth';

defineProps<{
  pending: boolean;
}>();

const emit = defineEmits<{
  submit: [payload: LoginPayload];
  switchMode: [];
}>();

const email = ref('');
const password = ref('');
const remember = ref(true);
const localError = ref('');

const isDisabled = computed<boolean>(() => email.value.trim() === '' || password.value.trim() === '');

/**
 * Валидирует поля формы и отправляет payload входа наружу.
 */
function submitForm(): void {
  localError.value = '';

  if (isDisabled.value) {
    localError.value = 'Заполни email и пароль, чтобы открыть врата гильдии.';

    return;
  }

  emit('submit', {
    email: email.value.trim(),
    password: password.value,
    remember: remember.value,
  });
}
</script>

<template>
  <form
    class="space-y-5"
    @submit.prevent="submitForm"
  >
    <div class="space-y-2">
      <h2 class="font-display text-[1.6rem] text-amber-50">
        Вход в гильдию
      </h2>
      <p class="text-sm leading-6 text-slate-300">
        Продолжи приключение с того места, где остановился.
      </p>
    </div>

    <AuthField
      v-model="email"
      autocomplete="email"
      label="Email"
      name="email"
      placeholder="hero@guild.quest"
      :icon="Mail"
    />

    <AuthField
      v-model="password"
      autocomplete="current-password"
      label="Пароль"
      name="password"
      placeholder="Введи пароль доступа"
      type="password"
      :icon="KeyRound"
    />

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <label class="inline-flex items-center gap-3 text-sm text-slate-300">
        <span class="relative inline-flex h-5 w-5 items-center justify-center">
          <input
            v-model="remember"
            class="peer sr-only"
            name="remember"
            type="checkbox"
          />
          <span class="absolute inset-0 rounded-md border border-amber-100/20 bg-white/5 transition peer-checked:border-amber-300/35 peer-checked:bg-amber-200/15" />
          <ScrollText class="relative h-3.5 w-3.5 text-transparent transition peer-checked:text-amber-100" />
        </span>
        Запомнить меня
      </label>

      <button
        class="text-sm text-amber-200/80 transition hover:text-amber-100"
        type="button"
      >
        Забыли пароль?
      </button>
    </div>

    <p
      v-if="localError"
      class="rounded-2xl border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
    >
      {{ localError }}
    </p>

    <div class="space-y-3">
      <button
        class="cta-primary w-full"
        :disabled="pending"
        type="submit"
      >
        {{ pending ? 'Открываем врата...' : 'Войти в мир' }}
      </button>

      <button
        class="cta-secondary w-full"
        type="button"
        @click="emit('switchMode')"
      >
        У меня еще нет аккаунта
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { KeyRound, Mail, UserRound } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AuthField from '@/components/auth/AuthField.vue';
import type { RegisterPayload } from '@/types/auth';

defineProps<{
  pending: boolean;
}>();

const emit = defineEmits<{
  submit: [payload: RegisterPayload];
  switchMode: [];
}>();

const heroName = ref('');
const email = ref('');
const password = ref('');
const localError = ref('');

const isDisabled = computed<boolean>(() => {
  return heroName.value.trim() === '' || email.value.trim() === '' || password.value.trim().length < 8;
});

/**
 * Валидирует поля формы и отправляет payload регистрации наружу.
 */
function submitForm(): void {
  localError.value = '';

  if (heroName.value.trim() === '' || email.value.trim() === '') {
    localError.value = 'Имя героя и email обязательны для записи в летопись.';

    return;
  }

  if (password.value.trim().length < 8) {
    localError.value = 'Пароль должен содержать минимум 8 символов.';

    return;
  }

  emit('submit', {
    email: email.value.trim(),
    heroName: heroName.value.trim(),
    password: password.value,
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
        Создание героя
      </h2>
      <p class="text-sm leading-6 text-slate-300">
        Открой доступ к играм, персонажам и кабинету мастера.
      </p>
    </div>

    <AuthField
      v-model="heroName"
      autocomplete="nickname"
      label="Имя героя"
      name="heroName"
      placeholder="Например, Лира Звездная"
      :icon="UserRound"
    />

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
      autocomplete="new-password"
      label="Пароль"
      name="password"
      placeholder="Минимум 8 символов"
      type="password"
      :icon="KeyRound"
    />

    <div class="rounded-[1.4rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.06),rgba(255,255,255,0.02))] px-4 py-3 text-sm leading-6 text-slate-300">
      Один аккаунт подходит и для игрока, и для мастера.
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
        :disabled="pending || isDisabled"
        type="submit"
      >
        {{ pending ? 'Записываем героя...' : 'Создать аккаунт' }}
      </button>

      <button
        class="cta-secondary w-full"
        type="button"
        @click="emit('switchMode')"
      >
        У меня уже есть аккаунт
      </button>
    </div>
  </form>
</template>

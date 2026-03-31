<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AuthBrand from '@/components/auth/AuthBrand.vue';
import AuthFrame from '@/components/auth/AuthFrame.vue';
import FantasyBackground from '@/components/auth/FantasyBackground.vue';
import ResetPasswordForm from '@/components/auth/ResetPasswordForm.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import type { ResetPasswordPayload } from '@/types/auth';

const route = useRoute();
const router = useRouter();

const {
  clearFeedback,
  feedbackMessage,
  feedbackTone,
  isAuthenticated,
  isPending,
  resetUserPassword,
} = useAuthSession();

const token = computed<string>(() => {
  const rawValue = route.query.token;

  return typeof rawValue === 'string' ? rawValue : '';
});

const email = computed<string>(() => {
  const rawValue = route.query.email;

  return typeof rawValue === 'string' ? rawValue : '';
});

const hasValidLink = computed<boolean>(() => token.value !== '' && email.value !== '');

/**
 * Возвращает пользователя на главный экран авторизации.
 */
async function backToLogin(): Promise<void> {
  clearFeedback();
  await router.push('/');
}

/**
 * Передает новый пароль в API и после успеха возвращает пользователя ко входу.
 */
async function handleResetPassword(payload: ResetPasswordPayload): Promise<void> {
  await resetUserPassword(payload);

  if (feedbackTone.value === 'success') {
    window.setTimeout(() => {
      void router.push('/');
    }, 1200);
  }
}

onMounted(async () => {
  clearFeedback();

  if (isAuthenticated.value) {
    await router.replace('/cabinet/player');
  }
});
</script>

<template>
  <main class="relative isolate min-h-screen overflow-hidden px-4 py-4 sm:px-6">
    <FantasyBackground />

    <div class="relative mx-auto flex min-h-[calc(100vh-2rem)] max-w-2xl items-center justify-center">
      <AuthFrame>
        <AuthBrand />

        <div class="arcane-divider" />

        <div class="space-y-1">
          <p class="text-xs uppercase text-amber-200/55">
            Смена пароля
          </p>
          <p class="max-w-md text-sm leading-5 text-slate-300">
            Используй ссылку из письма, чтобы задать новый пароль для аккаунта.
          </p>
        </div>

        <div
          v-if="feedbackMessage"
          :class="
            feedbackTone === 'error'
              ? 'border-rose-300/20 bg-rose-500/10 text-rose-100'
              : 'border-emerald-300/20 bg-emerald-500/10 text-emerald-100'
          "
          class="rounded-[1.2rem] border px-4 py-3 text-sm leading-5"
        >
          {{ feedbackMessage }}
        </div>

        <div
          v-if="!hasValidLink"
          class="space-y-3"
        >
          <div class="rounded-[1.2rem] border border-rose-300/20 bg-rose-500/10 px-4 py-3 text-sm leading-5 text-rose-100">
            Ссылка для сброса пароля недействительна или неполная.
          </div>

          <button
            class="cta-secondary w-full"
            type="button"
            @click="backToLogin"
          >
            Вернуться ко входу
          </button>
        </div>

        <ResetPasswordForm
          v-else
          :email="email"
          :pending="isPending"
          :token="token"
          @back="backToLogin"
          @submit="handleResetPassword"
        />
      </AuthFrame>
    </div>
  </main>
</template>

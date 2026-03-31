<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AuthBrand from '@/components/auth/AuthBrand.vue';
import AuthFrame from '@/components/auth/AuthFrame.vue';
import AuthTabs from '@/components/auth/AuthTabs.vue';
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue';
import FantasyBackground from '@/components/auth/FantasyBackground.vue';
import LoginForm from '@/components/auth/LoginForm.vue';
import RegisterForm from '@/components/auth/RegisterForm.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import type { AuthMode, AuthView, ForgotPasswordPayload, LoginPayload, RegisterPayload } from '@/types/auth';

const router = useRouter();
const mode = ref<AuthMode>('login');
const view = ref<AuthView>('login');

const {
  clearFeedback,
  ensureSessionLoaded,
  feedbackMessage,
  feedbackTone,
  isAuthenticated,
  isPending,
  loginUser,
  requestPasswordReset,
  registerUser,
} = useAuthSession();

/**
 * Переключает активный режим формы и очищает старые сообщения.
 */
function switchMode(nextMode: AuthMode): void {
  mode.value = nextMode;
  view.value = nextMode;
  clearFeedback();
}

/**
 * Передает payload входа в API-слой.
 */
async function handleLogin(payload: LoginPayload): Promise<void> {
  await loginUser(payload);

  if (isAuthenticated.value) {
    await router.push('/cabinet/player');
  }
}

/**
 * Передает payload регистрации в API-слой.
 */
async function handleRegister(payload: RegisterPayload): Promise<void> {
  await registerUser(payload);

  if (isAuthenticated.value) {
    await router.push('/cabinet/player');
  }
}

/**
 * Открывает экран восстановления пароля.
 */
function openForgotPassword(): void {
  view.value = 'forgot-password';
  clearFeedback();
}

/**
 * Возвращает пользователя на экран входа.
 */
function backToLogin(): void {
  view.value = 'login';
  mode.value = 'login';
  clearFeedback();
}

/**
 * Отправляет запрос на восстановление пароля.
 */
async function handleForgotPassword(payload: ForgotPasswordPayload): Promise<void> {
  await requestPasswordReset(payload);
}

onMounted(async () => {
  await ensureSessionLoaded();

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

        <div class="space-y-2.5">
          <div class="space-y-1">
            <p class="text-xs uppercase text-amber-200/55">
              Добро пожаловать
            </p>
            <p class="max-w-md text-sm leading-5 text-slate-300">
              Войди в аккаунт или зарегистрируйся, чтобы перейти в кабинет игрока.
            </p>
          </div>

          <AuthTabs
            v-if="view !== 'forgot-password'"
            :model-value="mode"
            @update:model-value="switchMode"
          />
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

        <Transition
          mode="out-in"
          name="mode-fade"
        >
          <LoginForm
            v-if="view === 'login'"
            key="login"
            :pending="isPending"
            @forgot-password="openForgotPassword"
            @submit="handleLogin"
          />

          <RegisterForm
            v-else-if="view === 'register'"
            key="register"
            :pending="isPending"
            @submit="handleRegister"
          />

          <ForgotPasswordForm
            v-else
            key="forgot-password"
            :pending="isPending"
            @back="backToLogin"
            @submit="handleForgotPassword"
          />
        </Transition>
      </AuthFrame>
    </div>
  </main>
</template>

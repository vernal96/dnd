<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Crown, DoorOpen, Flame, Sparkles } from 'lucide-vue-next';
import AuthBrand from '@/components/auth/AuthBrand.vue';
import AuthFrame from '@/components/auth/AuthFrame.vue';
import AuthTabs from '@/components/auth/AuthTabs.vue';
import FantasyBackground from '@/components/auth/FantasyBackground.vue';
import LoginForm from '@/components/auth/LoginForm.vue';
import RegisterForm from '@/components/auth/RegisterForm.vue';
import { useAuthSession } from '@/composables/useAuthSession';
import type { AuthMode, LoginPayload, RegisterPayload } from '@/types/auth';

const mode = ref<AuthMode>('login');

const {
  clearFeedback,
  currentUser,
  feedbackMessage,
  feedbackTone,
  isAuthenticated,
  isPending,
  loadSession,
  loginUser,
  logoutUser,
  registerUser,
} = useAuthSession();

/**
 * Переключает активный режим формы и очищает старые сообщения.
 */
function switchMode(nextMode: AuthMode): void {
  mode.value = nextMode;
  clearFeedback();
}

/**
 * Передает payload входа в API-слой.
 */
async function handleLogin(payload: LoginPayload): Promise<void> {
  await loginUser(payload);
}

/**
 * Передает payload регистрации в API-слой.
 */
async function handleRegister(payload: RegisterPayload): Promise<void> {
  await registerUser(payload);
}

/**
 * Завершает текущую пользовательскую сессию.
 */
async function handleLogout(): Promise<void> {
  await logoutUser();
}

onMounted(() => {
  void loadSession();
});
</script>

<template>
  <main class="relative isolate min-h-screen overflow-hidden px-4 py-8 sm:px-6 lg:px-8">
    <FantasyBackground />

    <div class="relative mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl items-center justify-center">
      <div class="grid w-full items-center gap-8 lg:grid-cols-[1.08fr_0.92fr]">
        <section class="hidden space-y-7 lg:block">
          <div class="max-w-xl space-y-5 text-slate-900/90">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-900/10 bg-white/35 px-4 py-2 text-xs uppercase tracking-[0.35em] text-amber-950/70 shadow-[0_18px_40px_rgba(111,64,12,0.08)] backdrop-blur-sm">
              <Flame class="h-4 w-4 text-amber-700" />
              Arcane Access Protocol
            </div>

            <div class="space-y-4">
              <h1 class="font-display text-5xl leading-[1.02] text-amber-950">
                Войди в летопись
                <span class="block text-[rgba(74,40,16,0.88)]">Table of Adventures</span>
              </h1>
              <p class="max-w-lg text-lg leading-8 text-amber-950/75">
                Один экран для входа в кампании, управления героями и возвращения в мир приключений.
                Атмосфера гильдии снаружи, чистый игровой интерфейс внутри.
              </p>
            </div>
          </div>

          <div class="grid max-w-xl gap-4 md:grid-cols-2">
            <div class="rounded-[1.75rem] border border-amber-900/10 bg-white/30 p-5 backdrop-blur-sm">
              <Sparkles class="mb-3 h-5 w-5 text-amber-700" />
              <p class="font-display text-xl text-amber-950">
                Игрок и мастер
              </p>
              <p class="mt-2 text-sm leading-6 text-amber-950/70">
                Одна учетная запись открывает доступ и к карточкам персонажей, и к мастерской панели.
              </p>
            </div>

            <div class="rounded-[1.75rem] border border-amber-900/10 bg-white/30 p-5 backdrop-blur-sm">
              <Crown class="mb-3 h-5 w-5 text-amber-700" />
              <p class="font-display text-xl text-amber-950">
                Сессия через API
              </p>
              <p class="mt-2 text-sm leading-6 text-amber-950/70">
                После входа браузер хранит cookie-сессию, а экран умеет читать ее состояние через backend API.
              </p>
            </div>
          </div>
        </section>

        <AuthFrame>
          <AuthBrand />

          <div class="arcane-divider" />

          <div class="space-y-4">
            <div class="space-y-2">
              <p class="text-xs uppercase tracking-[0.32em] text-amber-200/55">
                Добро пожаловать в гильдию
              </p>
              <p class="max-w-md text-sm leading-6 text-slate-300">
                Выбери режим входа, чтобы продолжить путешествие или создать нового героя для следующей кампании.
              </p>
            </div>

            <AuthTabs
              v-model="mode"
            />
          </div>

          <div
            v-if="feedbackMessage"
            :class="
              feedbackTone === 'error'
                ? 'border-rose-300/20 bg-rose-500/10 text-rose-100'
                : 'border-emerald-300/20 bg-emerald-500/10 text-emerald-100'
            "
            class="rounded-[1.4rem] border px-4 py-3 text-sm leading-6"
          >
            {{ feedbackMessage }}
          </div>

          <div
            v-if="isAuthenticated && currentUser"
            class="rounded-[1.6rem] border border-amber-200/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.06),rgba(255,255,255,0.02))] p-4"
          >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <div class="space-y-1">
                <p class="text-xs uppercase tracking-[0.28em] text-amber-200/55">
                  Сессия активна
                </p>
                <p class="font-display text-xl text-amber-50">
                  {{ currentUser.name }}
                </p>
                <p class="text-sm text-slate-300">
                  {{ currentUser.email }}
                </p>
              </div>

              <button
                class="cta-secondary shrink-0"
                type="button"
                @click="handleLogout"
              >
                <DoorOpen class="h-4 w-4" />
                Покинуть зал
              </button>
            </div>
          </div>

          <Transition
            mode="out-in"
            name="mode-fade"
          >
            <LoginForm
              v-if="mode === 'login'"
              key="login"
              :pending="isPending"
              @submit="handleLogin"
              @switch-mode="switchMode('register')"
            />

            <RegisterForm
              v-else
              key="register"
              :pending="isPending"
              @submit="handleRegister"
              @switch-mode="switchMode('login')"
            />
          </Transition>
        </AuthFrame>
      </div>
    </div>
  </main>
</template>

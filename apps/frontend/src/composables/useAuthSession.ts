import { computed, ref } from 'vue';
import * as authApi from '@/services/authApi';
import type { AuthSessionResponse, LoginPayload, RegisterPayload, SessionUser } from '@/types/auth';

/**
 * Управляет состоянием auth-сессии на клиенте.
 */
export function useAuthSession() {
  const session = ref<AuthSessionResponse>({
    authenticated: false,
    csrfToken: '',
    user: null,
  });
  const isPending = ref(false);
  const feedbackMessage = ref('');
  const feedbackTone = ref<'error' | 'success' | 'neutral'>('neutral');

  const currentUser = computed<SessionUser | null>(() => session.value.user);
  const isAuthenticated = computed<boolean>(() => session.value.authenticated);

  /**
   * Очищает пользовательское сообщение о результате операции.
   */
  function clearFeedback(): void {
    feedbackMessage.value = '';
    feedbackTone.value = 'neutral';
  }

  /**
   * Загружает состояние текущей пользовательской сессии.
   */
  async function loadSession(): Promise<void> {
    try {
      session.value = await authApi.fetchSession();
    } catch (error) {
      feedbackMessage.value = (error as Error).message;
      feedbackTone.value = 'error';
    }
  }

  /**
   * Выполняет вход пользователя и обновляет локальное состояние сессии.
   */
  async function loginUser(payload: LoginPayload): Promise<void> {
    isPending.value = true;
    clearFeedback();

    try {
      session.value = await authApi.login(payload);
      feedbackMessage.value = 'Печать гильдии подтверждена. Врата мира открыты.';
      feedbackTone.value = 'success';
    } catch (error) {
      feedbackMessage.value = (error as Error).message;
      feedbackTone.value = 'error';
    } finally {
      isPending.value = false;
    }
  }

  /**
   * Регистрирует нового пользователя и обновляет локальное состояние сессии.
   */
  async function registerUser(payload: RegisterPayload): Promise<void> {
    isPending.value = true;
    clearFeedback();

    try {
      session.value = await authApi.register(payload);
      feedbackMessage.value = 'Герой занесен в летопись. Сессия активирована.';
      feedbackTone.value = 'success';
    } catch (error) {
      feedbackMessage.value = (error as Error).message;
      feedbackTone.value = 'error';
    } finally {
      isPending.value = false;
    }
  }

  /**
   * Завершает текущую пользовательскую сессию.
   */
  async function logoutUser(): Promise<void> {
    isPending.value = true;
    clearFeedback();

    try {
      await authApi.logout();
      session.value = {
        authenticated: false,
        csrfToken: '',
        user: null,
      };
      feedbackMessage.value = 'Свиток доступа запечатан. До новой встречи в гильдии.';
      feedbackTone.value = 'success';
    } catch (error) {
      feedbackMessage.value = (error as Error).message;
      feedbackTone.value = 'error';
    } finally {
      isPending.value = false;
    }
  }

  return {
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
  };
}

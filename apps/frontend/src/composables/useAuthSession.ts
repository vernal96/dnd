import {computed, ref} from 'vue';
import {usePlayerInvitations} from '@/composables/usePlayerInvitations';
import * as authApi from '@/services/authApi';
import {disconnectRealtime} from '@/composables/useRealtimeSocket';
import type {
    AuthSessionResponse,
    ForgotPasswordPayload,
    LoginPayload,
    RegisterPayload,
    ResetPasswordPayload,
    SessionUser,
} from '@/types/auth';

const session = ref<AuthSessionResponse>({
    authenticated: false,
    csrfToken: '',
    user: null,
});
const isPending = ref(false);
const feedbackMessage = ref('');
const feedbackTone = ref<'error' | 'success' | 'neutral'>('neutral');
const hasLoadedSession = ref(false);

/**
 * Управляет состоянием auth-сессии на клиенте.
 */
export function useAuthSession() {
    const {resetInvitations} = usePlayerInvitations();
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
            hasLoadedSession.value = true;
            clearFeedback();
        } catch (error) {
            feedbackMessage.value = (error as Error).message;
            feedbackTone.value = 'error';
        }
    }

    /**
     * Гарантирует, что состояние сессии было загружено хотя бы один раз.
     */
    async function ensureSessionLoaded(): Promise<void> {
        if (hasLoadedSession.value) {
            return;
        }

        await loadSession();
    }

    /**
     * Выполняет вход пользователя и обновляет локальное состояние сессии.
     */
    async function loginUser(payload: LoginPayload): Promise<void> {
        isPending.value = true;
        clearFeedback();

        try {
            session.value = await authApi.login(payload);
            hasLoadedSession.value = true;
            feedbackMessage.value = 'Вход выполнен.';
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
            hasLoadedSession.value = true;
            feedbackMessage.value = 'Аккаунт создан.';
            feedbackTone.value = 'success';
        } catch (error) {
            feedbackMessage.value = (error as Error).message;
            feedbackTone.value = 'error';
        } finally {
            isPending.value = false;
        }
    }

    /**
     * Отправляет запрос на восстановление пароля.
     */
    async function requestPasswordReset(payload: ForgotPasswordPayload): Promise<void> {
        isPending.value = true;
        clearFeedback();

        try {
            const response = await authApi.forgotPassword(payload);
            feedbackMessage.value = response.message;
            feedbackTone.value = 'success';
        } catch (error) {
            feedbackMessage.value = (error as Error).message;
            feedbackTone.value = 'error';
        } finally {
            isPending.value = false;
        }
    }

    /**
     * Завершает сброс пароля по токену из письма.
     */
    async function resetUserPassword(payload: ResetPasswordPayload): Promise<void> {
        isPending.value = true;
        clearFeedback();

        try {
            const response = await authApi.resetPassword(payload);
            feedbackMessage.value = response.message;
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
            disconnectRealtime();
            resetInvitations();
            session.value = {
                authenticated: false,
                csrfToken: '',
                user: null,
            };
            hasLoadedSession.value = true;
            feedbackMessage.value = 'Вы вышли из аккаунта.';
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
        ensureSessionLoaded,
        feedbackMessage,
        feedbackTone,
        isAuthenticated,
        isPending,
        loadSession,
        loginUser,
        logoutUser,
        requestPasswordReset,
        resetUserPassword,
        registerUser,
    };
}

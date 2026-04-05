import type {
    AuthSessionResponse,
    ForgotPasswordPayload,
    LoginPayload,
    RegisterPayload,
    ResetPasswordPayload,
} from '@/types/auth';
import {fetchWithSession, resetHttpCsrfToken} from '@/services/httpApi';

/**
 * Возвращает текущее состояние пользовательской сессии.
 */
export function fetchSession(): Promise<AuthSessionResponse> {
    return fetchWithSession<AuthSessionResponse>('/auth/session');
}

/**
 * Выполняет вход пользователя.
 */
export function login(payload: LoginPayload): Promise<AuthSessionResponse> {
    return fetchWithSession<AuthSessionResponse>('/auth/login', {
        method: 'POST',
        body: JSON.stringify({
            login: payload.login,
            password: payload.password,
            remember: payload.remember,
        }),
    });
}

/**
 * Регистрирует нового пользователя.
 */
export function register(payload: RegisterPayload): Promise<AuthSessionResponse> {
    return fetchWithSession<AuthSessionResponse>('/auth/register', {
        method: 'POST',
        body: JSON.stringify({
            login: payload.login,
            email: payload.email,
            password: payload.password,
        }),
    });
}

/**
 * Завершает активную пользовательскую сессию.
 */
export function logout(): Promise<void> {
    const logoutRequest = fetchWithSession<void>('/auth/logout', {
        method: 'POST',
    });

    resetHttpCsrfToken();

    return logoutRequest;
}

/**
 * Запрашивает восстановление пароля для указанного email.
 */
export function forgotPassword(payload: ForgotPasswordPayload): Promise<{ message: string }> {
    return fetchWithSession<{ message: string }>('/auth/forgot-password', {
        method: 'POST',
        body: JSON.stringify({
            email: payload.email,
        }),
    });
}

/**
 * Завершает сброс пароля по токену из письма.
 */
export function resetPassword(payload: ResetPasswordPayload): Promise<{ message: string }> {
    return fetchWithSession<{ message: string }>('/auth/reset-password', {
        method: 'POST',
        body: JSON.stringify({
            token: payload.token,
            email: payload.email,
            password: payload.password,
            password_confirmation: payload.passwordConfirmation,
        }),
    });
}

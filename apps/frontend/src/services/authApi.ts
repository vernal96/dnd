import type { AuthSessionResponse, LoginPayload, RegisterPayload } from '@/types/auth';

const apiBaseUrl = import.meta.env.VITE_API_URL ?? 'http://localhost:8080';
let csrfToken = '';

/**
 * Выполняет запрос к backend auth API с cookie-сессией.
 */
async function request<TResponse>(path: string, init?: RequestInit): Promise<TResponse> {
  if ((init?.method ?? 'GET').toUpperCase() !== 'GET' && csrfToken === '') {
    await fetchSession();
  }

  const headers = new Headers(init?.headers);
  headers.set('Accept', 'application/json');

  if (init?.body !== undefined) {
    headers.set('Content-Type', 'application/json');
  }

  if (csrfToken !== '') {
    headers.set('X-CSRF-TOKEN', csrfToken);
  }

  const response = await fetch(`${apiBaseUrl}${path}`, {
    ...init,
    credentials: 'include',
    headers,
  });

  if (response.status === 204) {
    return undefined as TResponse;
  }

  const payload = (await response.json()) as Record<string, unknown>;

  if (!response.ok) {
    const message = typeof payload.message === 'string' ? payload.message : 'Не удалось выполнить запрос.';
    throw new Error(message);
  }

  if (typeof payload.csrfToken === 'string') {
    csrfToken = payload.csrfToken;
  }

  return payload as TResponse;
}

/**
 * Возвращает текущее состояние пользовательской сессии.
 */
export function fetchSession(): Promise<AuthSessionResponse> {
  return request<AuthSessionResponse>('/api/auth/session');
}

/**
 * Выполняет вход пользователя.
 */
export function login(payload: LoginPayload): Promise<AuthSessionResponse> {
  return request<AuthSessionResponse>('/api/auth/login', {
    method: 'POST',
    body: JSON.stringify({
      email: payload.email,
      password: payload.password,
      remember: payload.remember,
    }),
  });
}

/**
 * Регистрирует нового пользователя.
 */
export function register(payload: RegisterPayload): Promise<AuthSessionResponse> {
  return request<AuthSessionResponse>('/api/auth/register', {
    method: 'POST',
    body: JSON.stringify({
      hero_name: payload.heroName,
      email: payload.email,
      password: payload.password,
    }),
  });
}

/**
 * Завершает активную пользовательскую сессию.
 */
export function logout(): Promise<void> {
  return request<void>('/api/auth/logout', {
    method: 'POST',
  });
}

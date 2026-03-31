const apiBaseUrl = import.meta.env.VITE_API_URL ?? '/api';
let csrfToken = '';

/**
 * Нормализует базовый URL API без завершающего слеша.
 */
function resolveApiBaseUrl(): string {
  return apiBaseUrl.endsWith('/') ? apiBaseUrl.slice(0, -1) : apiBaseUrl;
}

/**
 * Сбрасывает локально сохраненный CSRF токен.
 */
export function resetHttpCsrfToken(): void {
  csrfToken = '';
}

/**
 * Обновляет локально сохраненный CSRF токен.
 */
export function setHttpCsrfToken(nextToken: string): void {
  csrfToken = nextToken;
}

/**
 * Возвращает локально сохраненный CSRF токен.
 */
export function getHttpCsrfToken(): string {
  return csrfToken;
}

/**
 * Выполняет HTTP-запрос к backend API с cookie-сессией.
 */
export async function fetchWithSession<TResponse>(
  path: string,
  init?: RequestInit,
  canRetry = true,
): Promise<TResponse> {
  if ((init?.method ?? 'GET').toUpperCase() !== 'GET' && csrfToken === '') {
    const sessionResponse = await fetchWithSession<{ csrfToken: string }>('/auth/session', undefined, false);
    setHttpCsrfToken(sessionResponse.csrfToken);
  }

  const headers = new Headers(init?.headers);
  headers.set('Accept', 'application/json');

  if (init?.body !== undefined) {
    headers.set('Content-Type', 'application/json');
  }

  if (csrfToken !== '') {
    headers.set('X-CSRF-TOKEN', csrfToken);
  }

  const response = await fetch(`${resolveApiBaseUrl()}${path}`, {
    ...init,
    credentials: 'include',
    headers,
  });

  if (response.status === 419 && canRetry && (init?.method ?? 'GET').toUpperCase() !== 'GET') {
    resetHttpCsrfToken();
    const sessionResponse = await fetchWithSession<{ csrfToken: string }>('/auth/session', undefined, false);
    setHttpCsrfToken(sessionResponse.csrfToken);

    return fetchWithSession<TResponse>(path, init, false);
  }

  if (response.status === 204) {
    return undefined as TResponse;
  }

  const contentType = response.headers.get('Content-Type') ?? '';

  if (!contentType.includes('application/json')) {
    throw new Error(response.ok ? 'Сервер вернул неожиданный ответ.' : 'Не удалось выполнить запрос к API.');
  }

  const payload = (await response.json()) as Record<string, unknown>;

  if (!response.ok) {
    const message = typeof payload.message === 'string' ? payload.message : 'Не удалось выполнить запрос.';
    throw new Error(message);
  }

  if (typeof payload.csrfToken === 'string') {
    setHttpCsrfToken(payload.csrfToken);
  }

  return payload as TResponse;
}

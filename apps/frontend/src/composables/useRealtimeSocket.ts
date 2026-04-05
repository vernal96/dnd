import type {RealtimeEventMessage} from '@/types/realtime';

type RealtimeHandler = (message: RealtimeEventMessage) => void;

let socket: WebSocket | null = null;
let reconnectTimerId: number | null = null;
let shouldReconnect = false;
const handlers = new Set<RealtimeHandler>();

/**
 * Возвращает URL для WebSocket-подключения текущего frontend-приложения.
 */
function resolveRealtimeUrl(): string {
    const configuredUrl = import.meta.env.VITE_WS_URL;

    if (typeof configuredUrl === 'string' && configuredUrl !== '') {
        return configuredUrl;
    }

    if (typeof window === 'undefined') {
        return 'ws://localhost/realtime';
    }

    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';

    return `${protocol}//${window.location.host}/realtime`;
}

/**
 * Планирует повторное подключение к realtime-серверу.
 */
function scheduleReconnect(): void {
    if (!shouldReconnect || reconnectTimerId !== null || typeof window === 'undefined') {
        return;
    }

    reconnectTimerId = window.setTimeout(() => {
        reconnectTimerId = null;
        connectRealtime();
    }, 1500);
}

/**
 * Обрабатывает входящее realtime-сообщение.
 */
function handleSocketMessage(event: MessageEvent<string>): void {
    try {
        const message = JSON.parse(event.data) as RealtimeEventMessage;
        handlers.forEach((handler) => handler(message));
    } catch {
        // Игнорируем некорректные сообщения transport-слоя.
    }
}

/**
 * Устанавливает realtime-подключение, если оно ещё не открыто.
 */
export function connectRealtime(): void {
    if (typeof window === 'undefined') {
        return;
    }

    shouldReconnect = true;

    if (socket !== null && (socket.readyState === WebSocket.OPEN || socket.readyState === WebSocket.CONNECTING)) {
        return;
    }

    socket = new WebSocket(resolveRealtimeUrl());
    socket.addEventListener('message', handleSocketMessage);
    socket.addEventListener('close', () => {
        socket = null;
        scheduleReconnect();
    });
    socket.addEventListener('error', () => {
        socket?.close();
    });
}

/**
 * Разрывает realtime-подключение и отключает автопереподключение.
 */
export function disconnectRealtime(): void {
    shouldReconnect = false;

    if (reconnectTimerId !== null && typeof window !== 'undefined') {
        window.clearTimeout(reconnectTimerId);
        reconnectTimerId = null;
    }

    socket?.close();
    socket = null;
}

/**
 * Подписывает обработчик на realtime-сообщения.
 */
export function subscribeRealtime(handler: RealtimeHandler): () => void {
    handlers.add(handler);

    return () => {
        handlers.delete(handler);
    };
}

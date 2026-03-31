import { createServer } from 'node:http';
import { WebSocketServer } from 'ws';
import { createClient } from 'redis';

const port = Number.parseInt(process.env.PORT ?? '8081', 10);
const apiBaseUrl = (process.env.API_BASE_URL ?? 'http://api:8080').replace(/\/$/, '');
const frontendOrigin = process.env.FRONTEND_ORIGIN ?? 'http://localhost';
const redisUrl = process.env.REDIS_URL ?? 'redis://redis:6379';
const redisChannelPattern = '*realtime.user-notifications';
const websocketPath = '/realtime';

/** @type {Map<number, Set<import('ws').WebSocket>>} */
const userSockets = new Map();

const server = createServer((request, response) => {
  if (request.url === '/health') {
    response.writeHead(200, { 'Content-Type': 'application/json' });
    response.end(JSON.stringify({ status: 'ok' }));

    return;
  }

  response.writeHead(404);
  response.end();
});

const websocketServer = new WebSocketServer({ noServer: true });

/**
 * Возвращает данные текущей сессии пользователя по cookie из upgrade-запроса.
 *
 * @param {import('node:http').IncomingMessage} request
 * @returns {Promise<{ id: number } | null>}
 */
async function resolveAuthenticatedUser(request) {
  const response = await fetch(`${apiBaseUrl}/api/auth/session`, {
    headers: {
      Accept: 'application/json',
      Cookie: request.headers.cookie ?? '',
      Origin: typeof request.headers.origin === 'string' && request.headers.origin !== '' ? request.headers.origin : frontendOrigin,
      Referer:
        typeof request.headers.origin === 'string' && request.headers.origin !== ''
          ? `${request.headers.origin}/`
          : `${frontendOrigin}/`,
    },
  });

  if (!response.ok) {
    return null;
  }

  const payload = await response.json();

  if (payload.authenticated !== true || payload.user === null || typeof payload.user.id !== 'number') {
    return null;
  }

  return { id: payload.user.id };
}

/**
 * Добавляет сокет в список активных подключений пользователя.
 *
 * @param {number} userId
 * @param {import('ws').WebSocket} socket
 */
function attachSocket(userId, socket) {
  const sockets = userSockets.get(userId) ?? new Set();
  sockets.add(socket);
  userSockets.set(userId, sockets);

  socket.on('close', () => {
    const currentSockets = userSockets.get(userId);

    if (currentSockets === undefined) {
      return;
    }

    currentSockets.delete(socket);

    if (currentSockets.size === 0) {
      userSockets.delete(userId);
    }
  });
}

/**
 * Отправляет realtime-сообщение целевым пользователям.
 *
 * @param {{ event: string; payload: Record<string, unknown>; targetUserIds: number[] }} message
 */
function broadcastToTargetUsers(message) {
  const serializedMessage = JSON.stringify({
    event: message.event,
    payload: message.payload,
  });

  for (const userId of message.targetUserIds) {
    const sockets = userSockets.get(userId);

     console.log(`[realtime] Broadcasting ${message.event} to user ${userId} (${sockets?.size ?? 0} socket(s))`);

    if (sockets === undefined) {
      continue;
    }

    for (const socket of sockets) {
      if (socket.readyState === socket.OPEN) {
        socket.send(serializedMessage);
      }
    }
  }
}

/**
 * Запускает подписку на Redis-канал с пользовательскими уведомлениями.
 */
async function startRedisSubscription() {
  const subscriber = createClient({
    url: redisUrl,
  });

  subscriber.on('error', (error) => {
    console.error('[realtime] Redis subscriber error:', error);
  });

  await subscriber.connect();
  await subscriber.pSubscribe(redisChannelPattern, (message, channel) => {
    try {
      const payload = JSON.parse(message);

      console.log(`[realtime] Redis event received on ${channel}: ${payload.event ?? 'unknown'}`);

      if (!Array.isArray(payload.targetUserIds) || typeof payload.event !== 'string' || typeof payload.payload !== 'object' || payload.payload === null) {
        return;
      }

      broadcastToTargetUsers({
        event: payload.event,
        payload: payload.payload,
        targetUserIds: payload.targetUserIds.filter((value) => typeof value === 'number'),
      });
    } catch (error) {
      console.error('[realtime] Failed to parse message:', error);
    }
  });
}

server.on('upgrade', async (request, socket, head) => {
  const requestPathname = new URL(request.url ?? '/', 'http://localhost').pathname;

  if (requestPathname !== websocketPath) {
    console.warn(`[realtime] Rejecting upgrade for unexpected path: ${request.url ?? '<empty>'}`);
    socket.write('HTTP/1.1 404 Not Found\r\n\r\n');
    socket.destroy();

    return;
  }

  try {
    console.log(`[realtime] Upgrade request received for ${requestPathname}`);
    const user = await resolveAuthenticatedUser(request);

    if (user === null) {
      console.warn('[realtime] Upgrade rejected: unauthenticated user');
      socket.write('HTTP/1.1 401 Unauthorized\r\n\r\n');
      socket.destroy();

      return;
    }

    websocketServer.handleUpgrade(request, socket, head, (websocket) => {
      console.log(`[realtime] WebSocket connected for user ${user.id}`);
      attachSocket(user.id, websocket);
      websocket.send(JSON.stringify({
        event: 'realtime.connected',
        payload: {
          userId: user.id,
        },
      }));
    });
  } catch (error) {
    console.error('[realtime] Upgrade failed:', error);
    socket.write('HTTP/1.1 500 Internal Server Error\r\n\r\n');
    socket.destroy();
  }
});

websocketServer.on('connection', (socket) => {
  socket.on('error', () => {
    socket.close();
  });
});

const heartbeatTimer = setInterval(() => {
  websocketServer.clients.forEach((socket) => {
    if (socket.readyState === socket.OPEN) {
      socket.ping();
    }
  });
}, 30000);

process.on('SIGTERM', () => {
  clearInterval(heartbeatTimer);
  websocketServer.close();
  server.close(() => process.exit(0));
});

await startRedisSubscription();
server.listen(port, '0.0.0.0', () => {
  console.log(`[realtime] WebSocket server is listening on :${port}`);
});

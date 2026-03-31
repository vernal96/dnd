# Realtime service

Сюда подключается отдельный WebSocket/realtime слой для игровых intent-команд, presence и доставки подтвержденных state diff.

Ожидаемые зависимости из compose:

- `API_BASE_URL=http://api:8080`
- `REDIS_URL=redis://redis:6379`
- `KAFKA_BROKERS=kafka:9092`

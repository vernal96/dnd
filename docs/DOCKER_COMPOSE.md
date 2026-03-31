# Docker Compose для DnD service

## От чего отталкивался

`docs/01_Domain_Model_DND_Service.docx` фиксирует целевой технологический контур: `PHP backend`, `TypeScript/JavaScript frontend`, `Canvas`, `WebSocket`, `Postgres`, `Redis`, `Kafka`.

`docs/02_Runtime_Rules_DND_Service.docx` добавляет требования к runtime:

- синхронная обработка игровых команд на сервере;
- `Redis` для `presence`, `pub/sub`, кэша активной игры и блокировок;
- `Kafka` для доменных событий и фоновых подписчиков;
- базовые topics: `game.events`, `game.audit`, `game.notifications`, `game.analytics`, `game.saves`.

`docs/03_Combat_Draft_v1_DND_Service.docx` подтверждает, что combat ложится поверх того же runtime-контура, а значит отдельная инфраструктура под бой не нужна.

## Что входит в compose

- `postgres`: постоянное хранилище доменной модели, снапшотов и аудита.
- `redis`: presence, быстрый кэш runtime-состояния, примитивы блокировок.
- `kafka`: шина событий для аудита, аналитики, уведомлений и фоновых задач.
- `init-kafka-topics`: одноразовый инициализатор топиков из runtime-документа.
- `api`: backend на `Laravel 12`.
- `realtime`: будущий WebSocket/realtime слой.
- `worker`: отдельная роль под consumer-ы Kafka, снапшоты и проекции.
- `frontend`: будущий Canvas/UI клиент.
- `kafka-ui`: опциональный инструмент для локальной диагностики Kafka.

## Как использовать

1. Скопировать `.env.example` в `.env`.
2. Поднять базовую инфраструктуру:

```bash
docker compose up -d postgres redis kafka init-kafka-topics
```

3. Поднять прикладные контейнеры:

```bash
docker compose --profile app up -d
```

4. Для UI Kafka:

```bash
docker compose --profile tools up -d kafka-ui
```

## Почему именно так

- Инфраструктурные сервисы можно запускать уже сейчас, даже без исходников приложения.
- Backend уже инициализирован как `Laravel 12`, для него добавлен отдельный `Dockerfile` с нужными PHP-расширениями.
- Прикладные сервисы вынесены в профиль `app`, а `realtime` и `frontend` пока оставлены как минимальные заглушки.
- `worker` выделен отдельно, потому что в документах явно есть асинхронные подписчики, аудит, аналитика и снапшоты.
- Kafka topics создаются явно, потому что в документах уже зафиксированы имена и ответственность этих потоков.

## Что стоит сделать следующим шагом

- Реализовать `apps/realtime` и `apps/frontend`.
- Добавить миграции Postgres и bootstrap для Redis/Kafka consumer groups.

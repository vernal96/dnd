# Frontend service

SSR SPA-клиент на `Vue 3 + Vite + TypeScript + Tailwind CSS`.

## Команды

- `npm install`
- `npm run dev`
- `docker compose --profile app up -d nginx frontend api`

## Переменные окружения

- `VITE_API_URL=/api` при запуске через `nginx`
- `VITE_API_URL=http://localhost:8080` для standalone dev-режима без `nginx`
- `VITE_WS_URL=ws://localhost:8081`
- `PORT=5173`
- `HOST=0.0.0.0`

## Что реализовано

- SSR-рендеринг без `Nuxt`
- SPA-экран авторизации/регистрации
- кабинеты игрока и ГМ
- session-based auth через backend API
- browser-origin доступ к API ограничен frontend origin
- компонентная структура для дальнейшего роста UI

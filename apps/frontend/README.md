# Frontend service

SSR SPA-клиент на `Vue 3 + Vite + TypeScript + Tailwind CSS`.

## Команды

- `npm install`
- `npm run dev`
- `docker compose --profile app up -d frontend`

## Переменные окружения

- `VITE_API_URL=http://localhost:8080`
- `VITE_WS_URL=ws://localhost:8081`
- `PORT=5173`
- `HOST=0.0.0.0`

## Что реализовано

- SSR-рендеринг без `Nuxt`
- SPA-экран авторизации/регистрации
- session-based auth через backend API
- browser-origin доступ к API ограничен frontend origin `http://localhost:5173`
- компонентная структура для дальнейшего роста UI

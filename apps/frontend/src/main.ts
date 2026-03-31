import { createSSRApp } from 'vue';
import { createMemoryHistory, createRouter, createWebHistory } from 'vue-router';
import App from '@/App.vue';
import AuthPage from '@/pages/AuthPage.vue';
import '@/styles/tailwind.css';

/**
 * Создает приложение и роутер для SSR и клиентской гидрации.
 */
export function createApplication() {
  const app = createSSRApp(App);
  const router = createRouter({
    history: import.meta.env.SSR ? createMemoryHistory() : createWebHistory(),
    routes: [
      {
        path: '/',
        name: 'auth',
        component: AuthPage,
      },
    ],
  });

  app.use(router);

  return { app, router };
}

import { createApplication } from '@/main';

const { app, router } = createApplication();

void router.isReady().then(() => {
  app.mount('#app');
});

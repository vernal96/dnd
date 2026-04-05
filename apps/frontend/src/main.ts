import {createSSRApp} from 'vue';
import {createMemoryHistory, createRouter, createWebHistory} from 'vue-router';
import App from '@/App.vue';
import AuthPage from '@/pages/AuthPage.vue';
import GmGamePage from '@/pages/GmGamePage.vue';
import GmCabinetPage from '@/pages/GmCabinetPage.vue';
import GmSceneEditorPage from '@/pages/GmSceneEditorPage.vue';
import GmSceneRuntimePage from '@/pages/GmSceneRuntimePage.vue';
import PlayerCabinetPage from '@/pages/PlayerCabinetPage.vue';
import PlayerSceneRuntimePage from '@/pages/PlayerSceneRuntimePage.vue';
import ResetPasswordPage from '@/pages/ResetPasswordPage.vue';
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
            {
                path: '/reset-password',
                name: 'reset-password',
                component: ResetPasswordPage,
            },
            {
                path: '/cabinet/player',
                name: 'player-cabinet',
                component: PlayerCabinetPage,
            },
            {
                path: '/cabinet/player/games/:id/runtime',
                name: 'player-scene-runtime',
                component: PlayerSceneRuntimePage,
            },
            {
                path: '/cabinet/gm',
                name: 'gm-cabinet',
                component: GmCabinetPage,
            },
            {
                path: '/cabinet/gm/games/:id',
                name: 'gm-game',
                component: GmGamePage,
            },
            {
                path: '/cabinet/gm/games/:id/scenes/:sceneId',
                name: 'gm-scene-editor',
                component: GmSceneEditorPage,
            },
            {
                path: '/cabinet/gm/games/:id/runtime',
                name: 'gm-scene-runtime',
                component: GmSceneRuntimePage,
            },
        ],
    });

    app.use(router);

    return {app, router};
}

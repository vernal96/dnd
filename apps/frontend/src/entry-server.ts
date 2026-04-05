import {renderToString} from '@vue/server-renderer';
import {createApplication} from '@/main';

/**
 * Рендерит приложение в HTML на стороне сервера.
 */
export async function render(url: string): Promise<{ head: string; html: string }> {
    const {app, router} = createApplication();

    await router.push(url);
    await router.isReady();

    const html = await renderToString(app);

    return {
        head: '',
        html,
    };
}

import fs from 'node:fs/promises';
import path from 'node:path';
import express from 'express';
import { createServer as createViteServer, ViteDevServer } from 'vite';

type RenderResult = {
  head?: string;
  html: string;
};

type SsrModule = {
  render: (url: string) => Promise<RenderResult>;
};

/**
 * Возвращает host и port из env и аргументов командной строки.
 */
function resolveServerOptions(): { host: string; port: number } {
  const args = process.argv.slice(2);
  const argHostIndex = args.indexOf('--host');
  const argPortIndex = args.indexOf('--port');

  const host = (argHostIndex >= 0 ? args[argHostIndex + 1] : process.env.HOST) ?? '0.0.0.0';
  const portValue = argPortIndex >= 0 ? args[argPortIndex + 1] : process.env.PORT;
  const port = Number.parseInt(portValue ?? '5173', 10);

  return {
    host,
    port: Number.isNaN(port) ? 5173 : port,
  };
}

/**
 * Создает HTML-ответ SSR для указанного URL.
 */
async function renderPage(
  url: string,
  template: string,
  render: SsrModule['render'],
): Promise<string> {
  const rendered = await render(url);

  return template
    .replace('<!--app-head-->', rendered.head ?? '')
    .replace('<!--app-html-->', rendered.html);
}

/**
 * Запускает SSR-сервер фронтенда в dev или production режиме.
 */
async function bootstrap(): Promise<void> {
  const rootDirectory = process.cwd();
  const isProduction = process.env.NODE_ENV === 'production';
  const { host, port } = resolveServerOptions();
  const app = express();
  let viteServer: ViteDevServer | undefined;

  if (!isProduction) {
    viteServer = await createViteServer({
      root: rootDirectory,
      appType: 'custom',
      server: {
        middlewareMode: true,
      },
    });

    app.use(viteServer.middlewares);
  } else {
    app.use('/assets', express.static(path.resolve(rootDirectory, 'dist/client/assets')));
  }

  app.use('*', async (request, response) => {
    try {
      const requestUrl = request.originalUrl;

      if (viteServer !== undefined) {
        const templatePath = path.resolve(rootDirectory, 'index.html');
        const rawTemplate = await fs.readFile(templatePath, 'utf-8');
        const transformedTemplate = await viteServer.transformIndexHtml(requestUrl, rawTemplate);
        const ssrModule = (await viteServer.ssrLoadModule('/src/entry-server.ts')) as SsrModule;
        const html = await renderPage(requestUrl, transformedTemplate, ssrModule.render);

        response.status(200).setHeader('Content-Type', 'text/html').end(html);

        return;
      }

      const templatePath = path.resolve(rootDirectory, 'dist/client/index.html');
      const rawTemplate = await fs.readFile(templatePath, 'utf-8');
      const ssrModule = (await import(path.resolve(rootDirectory, 'dist/server/entry-server.js'))) as SsrModule;
      const html = await renderPage(requestUrl, rawTemplate, ssrModule.render);

      response.status(200).setHeader('Content-Type', 'text/html').end(html);
    } catch (error) {
      viteServer?.ssrFixStacktrace(error as Error);
      response.status(500).setHeader('Content-Type', 'text/plain').end((error as Error).stack ?? 'SSR error');
    }
  });

  app.listen(port, host, () => {
    // eslint-disable-next-line no-console
    console.log(`Table of Adventures frontend is listening on http://${host}:${port}`);
  });
}

void bootstrap();

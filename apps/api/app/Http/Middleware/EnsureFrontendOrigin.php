<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ограничивает браузерный доступ к API только разрешенными frontend-origin.
 */
final class EnsureFrontendOrigin
{
	/**
	 * Проверяет origin или referer запроса перед доступом к API.
	 *
	 * @param Closure(Request): Response $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$allowedOrigins = $this->resolveAllowedOrigins();
		$requestHost = rtrim($request->getSchemeAndHttpHost(), '/');
		$requestOrigin = $this->resolveRequestOrigin($request);

		if ($requestOrigin !== null && !in_array($requestOrigin, $allowedOrigins, true)) {
			return new JsonResponse([
				'message' => 'Доступ к API разрешен только для доверенного frontend-origin.',
			], 403);
		}

		if ($requestOrigin === null && !in_array($requestHost, $allowedOrigins, true)) {
			return new JsonResponse([
				'message' => 'Доступ к API разрешен только для доверенного frontend-origin.',
			], 403);
		}

		return $next($request);
	}

	/**
	 * Возвращает список разрешенных frontend-origin.
	 *
	 * @return list<string>
	 */
	private function resolveAllowedOrigins(): array
	{
		$configuredOrigins = config('cors.allowed_origins', []);

		return array_values(array_filter(
			array_map(
				static fn(mixed $origin): string => is_string($origin) ? rtrim($origin, '/') : '',
				$configuredOrigins,
			),
			static fn(string $origin): bool => $origin !== '',
		));
	}

	/**
	 * Извлекает origin из заголовков Origin или Referer.
	 */
	private function resolveRequestOrigin(Request $request): ?string
	{
		$originHeader = $request->headers->get('Origin');

		if (is_string($originHeader) && $originHeader !== '') {
			return rtrim($originHeader, '/');
		}

		$refererHeader = $request->headers->get('Referer');

		if (!is_string($refererHeader) || $refererHeader === '') {
			$requestHost = $request->getSchemeAndHttpHost();

			return $requestHost !== '' ? rtrim($requestHost, '/') : null;
		}

		$scheme = parse_url($refererHeader, PHP_URL_SCHEME);
		$host = parse_url($refererHeader, PHP_URL_HOST);
		$port = parse_url($refererHeader, PHP_URL_PORT);

		if (!is_string($scheme) || !is_string($host) || $scheme === '' || $host === '') {
			return null;
		}

		if ($port === null) {
			return sprintf('%s://%s', $scheme, $host);
		}

		return sprintf('%s://%s:%d', $scheme, $host, $port);
	}
}

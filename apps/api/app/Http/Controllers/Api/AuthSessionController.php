<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Auth\AuthSessionService;
use App\Data\Auth\LoginCredentialsData;
use App\Data\Auth\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

/**
 * Обрабатывает вход, регистрацию и жизненный цикл пользовательской сессии.
 */
final class AuthSessionController extends Controller
{
    /**
     * Создает контроллер авторизации.
     */
    public function __construct(
        private readonly AuthSessionService $authSessionService,
    ) {}

    /**
     * Возвращает текущее состояние пользовательской сессии.
     */
    public function show(Request $request): JsonResponse
    {
        $session = $this->authSessionService->getSessionState($request);

        return response()->json($session->toArray());
    }

    /**
     * Выполняет вход пользователя по email и паролю.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $session = $this->authSessionService->login(
                LoginCredentialsData::fromArray($request->validated()),
                $request,
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($session->toArray());
    }

    /**
     * Регистрирует нового пользователя и сразу открывает сессию.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $session = $this->authSessionService->register(
            RegisterUserData::fromArray($request->validated()),
            $request,
        );

        return response()->json($session->toArray(), 201);
    }

    /**
     * Завершает текущую пользовательскую сессию.
     */
    public function destroy(Request $request): Response
    {
        $this->authSessionService->logout($request);

        return response()->noContent();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Auth\AuthSessionService;
use App\Data\Auth\ForgotPasswordData;
use App\Data\Auth\LoginCredentialsData;
use App\Data\Auth\RegisterUserData;
use App\Data\Auth\ResetPasswordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

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
        } catch (Throwable $throwable) {
            return $this->buildServiceFailureResponse($throwable);
        }

        return response()->json($session->toArray());
    }

    /**
     * Регистрирует нового пользователя и сразу открывает сессию.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $session = $this->authSessionService->register(
                RegisterUserData::fromArray($request->validated()),
                $request,
            );
        } catch (Throwable $throwable) {
            return $this->buildServiceFailureResponse($throwable);
        }

        return response()->json($session->toArray(), 201);
    }

    /**
     * Принимает запрос на восстановление пароля.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $message = $this->authSessionService->requestPasswordReset(
                ForgotPasswordData::fromArray($request->validated()),
            );
        } catch (Throwable $throwable) {
            return $this->buildServiceFailureResponse($throwable);
        }

        return response()->json([
            'message' => $message,
        ], 202);
    }

    /**
     * Завершает сброс пароля по токену из письма.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authSessionService->resetPassword(
                ResetPasswordData::fromArray($request->validated()),
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $throwable) {
            return $this->buildServiceFailureResponse($throwable);
        }

        return response()->json([
            'message' => 'Пароль обновлен. Теперь можно войти с новым паролем.',
        ]);
    }

    /**
     * Завершает текущую пользовательскую сессию.
     */
    public function destroy(Request $request): Response
    {
        try {
            $this->authSessionService->logout($request);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Не удалось завершить пользовательскую сессию.',
            ], 500);
        }

        return response()->noContent();
    }

    /**
     * Преобразует техническую ошибку сервиса в управляемый JSON-ответ.
     */
    private function buildServiceFailureResponse(Throwable $throwable): JsonResponse
    {
        report($throwable);

        return response()->json([
            'message' => 'Сервис авторизации временно недоступен.',
        ], 500);
    }
}

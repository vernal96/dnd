<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Game\GameInvitationService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

/**
 * Отдает API приглашений в игровые столы для игрока.
 */
final class GameInvitationController extends Controller
{
    /**
     * Создает контроллер API приглашений.
     */
    public function __construct(
        private readonly GameInvitationService $gameInvitationService,
    ) {}

    /**
     * Возвращает список непринятых приглашений текущего игрока.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('web');

        return response()->json(
            $this->gameInvitationService->getInvitationsForPlayer($user),
        );
    }

    /**
     * Принимает приглашение и добавляет игрока в игру.
     */
    public function accept(string $token, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('web');

        try {
            $invitation = $this->gameInvitationService->acceptInvitation($token, $user);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Не удалось принять приглашение.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($invitation === null) {
            return response()->json([
                'message' => 'Приглашение не найдено.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($invitation);
    }

    /**
     * Отклоняет приглашение игрока без вступления в игру.
     */
    public function decline(string $token, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('web');

        try {
            $invitation = $this->gameInvitationService->declineInvitation($token, $user);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'Не удалось отклонить приглашение.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($invitation === null) {
            return response()->json([
                'message' => 'Приглашение не найдено.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($invitation);
    }
}

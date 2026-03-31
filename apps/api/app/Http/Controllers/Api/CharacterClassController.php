<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\CharacterClassCatalog;
use App\Domain\Catalog\AbstractCharacterClass;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Отдает API справочника классов и подклассов персонажей.
 */
final class CharacterClassController extends Controller
{
    /**
     * Создает контроллер справочника классов персонажей.
     */
    public function __construct(
        private readonly CharacterClassCatalog $characterClassCatalog,
    ) {}

    /**
     * Возвращает список активных классов персонажей вместе с подклассами.
     */
    public function index(): JsonResponse
    {
        return response()->json(array_map(
            static fn (AbstractCharacterClass $characterClass): array => $characterClass->toArray(),
            $this->characterClassCatalog->getActiveClasses(),
        ));
    }

    /**
     * Возвращает один активный класс персонажа вместе с его подклассами.
     */
    public function show(string $characterClass): JsonResponse
    {
        $classDefinition = $this->characterClassCatalog->findActiveClassByCode($characterClass);

        if ($classDefinition === null) {
            return response()->json([
                'message' => 'Класс персонажа не найден.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($classDefinition->toArray());
    }
}

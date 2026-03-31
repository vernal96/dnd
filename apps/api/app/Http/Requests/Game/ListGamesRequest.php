<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидирует query-параметры списка игр кабинета мастера.
 */
final class ListGamesRequest extends FormRequest
{
    /**
     * Определяет, разрешено ли выполнение запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации для списка игр.
     *
     * @return array<string, array<int, Rule|string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['draft', 'active', 'paused', 'completed'])],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидирует payload для приглашения участника в игру.
 */
final class InviteGameMemberRequest extends FormRequest
{
    /**
     * Определяет, разрешено ли выполнение запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации для приглашения участника.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }
}

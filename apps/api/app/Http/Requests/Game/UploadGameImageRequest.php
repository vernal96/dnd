<?php

declare(strict_types=1);

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * Валидирует запрос на загрузку изображения игры.
 */
final class UploadGameImageRequest extends FormRequest
{
    /**
     * Определяет, разрешено ли выполнение запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации для загрузки изображения игры.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:10240'],
        ];
    }

    /**
     * Возвращает типизированный файл изображения из запроса.
     */
    public function getFile(): UploadedFile
    {
        /** @var UploadedFile $file */
        $file = $this->file('file');

        return $file;
    }
}

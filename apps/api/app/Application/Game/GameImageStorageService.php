<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Data\Game\GameImageFileData;
use App\Data\Game\StoredGameImageData;
use App\Data\Game\UploadGameImageData;
use App\Models\Game;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Управляет хранением изображений, привязанных к игровым столам.
 */
final class GameImageStorageService
{
	private const string DISK_NAME = 'game_images';

	/**
	 * Возвращает список изображений игры, доступных текущему пользователю.
	 *
	 * @return list<StoredGameImageData>|null
	 */
	public function getImages(int $gameId, User $user): ?array
	{
		if (!$this->userCanAccessGame($gameId, $user)) {
			return null;
		}

		$disk = $this->getDisk();
		$directory = $this->buildDirectory($gameId);
		$files = $disk->files($directory);
		sort($files);

		return array_map(
			fn(string $path): StoredGameImageData => $this->buildStoredImageData($gameId, $path, $disk),
			$files,
		);
	}

	/**
	 * Проверяет, что пользователь состоит в игре или является ее мастером.
	 */
	private function userCanAccessGame(int $gameId, User $user): bool
	{
		return Game::query()
			->where('id', $gameId)
			->where(static function ($query) use ($user): void {
				$query
					->where('gm_user_id', $user->id)
					->orWhereHas('members', static function ($memberQuery) use ($user): void {
						$memberQuery->where('user_id', $user->id);
					});
			})
			->exists();
	}

	/**
	 * Возвращает файловый диск для хранения игровых изображений.
	 */
	private function getDisk(): Filesystem
	{
		return Storage::disk(self::DISK_NAME);
	}

	/**
	 * Возвращает путь каталога изображений игры на диске.
	 */
	private function buildDirectory(int $gameId): string
	{
		return 'games/' . $gameId;
	}

	/**
	 * Собирает DTO сохраненного изображения из файлового пути.
	 */
	private function buildStoredImageData(
		int        $gameId,
		string     $path,
		Filesystem $disk,
		?string    $originalName = null,
	): StoredGameImageData
	{
		$fileName = basename($path);
		$mimeType = $this->resolveMimeType($disk, $path);

		return new StoredGameImageData(
			fileName: $fileName,
			originalName: $originalName ?? $fileName,
			mimeType: $mimeType,
			fileSize: $disk->size($path),
			downloadUrl: '/api/games/' . $gameId . '/images/' . $fileName,
		);
	}

	/**
	 * Сохраняет новое изображение в каталог указанной игры.
	 *
	 * @throws RuntimeException Если игра не найдена или файл не удалось сохранить.
	 */
	public function storeImage(int $gameId, UploadGameImageData $data, User $user): StoredGameImageData
	{
		if (!$this->userOwnsGame($gameId, $user)) {
			throw new RuntimeException('Игра не найдена или недоступна для загрузки изображения.');
		}

		$disk = $this->getDisk();
		$directory = $this->buildDirectory($gameId);
		$extension = $data->file->guessExtension();

		if ($extension === null || $extension === '') {
			$extension = $data->file->getClientOriginalExtension();
		}

		if ($extension === '') {
			$extension = 'bin';
		}

		$fileName = Str::lower((string)Str::uuid()) . '.' . Str::lower($extension);
		$storedPath = $disk->putFileAs($directory, $data->file, $fileName);

		if (!is_string($storedPath) || $storedPath === '') {
			throw new RuntimeException('Не удалось сохранить изображение игры.');
		}

		return $this->buildStoredImageData($gameId, $storedPath, $disk, $data->file->getClientOriginalName());
	}

	/**
	 * Проверяет, что пользователь является мастером указанной игры.
	 */
	private function userOwnsGame(int $gameId, User $user): bool
	{
		return Game::query()
			->where('id', $gameId)
			->where('gm_user_id', $user->id)
			->exists();
	}

	/**
	 * Возвращает файл изображения игры, если он доступен текущему пользователю.
	 */
	public function findImage(int $gameId, string $fileName, User $user): ?GameImageFileData
	{
		if (!$this->userCanAccessGame($gameId, $user)) {
			return null;
		}

		$safeFileName = basename($fileName);
		$path = $this->buildDirectory($gameId) . '/' . $safeFileName;
		$disk = $this->getDisk();

		if (!$disk->exists($path)) {
			return null;
		}

		return new GameImageFileData(
			absolutePath: $disk->path($path),
			fileName: $safeFileName,
			mimeType: $this->resolveMimeType($disk, $path),
		);
	}

	/**
	 * Возвращает MIME-тип файла или безопасное значение по умолчанию.
	 */
	private function resolveMimeType(Filesystem $disk, string $path): string
	{
		$mimeType = $disk->mimeType($path);

		return is_string($mimeType) && $mimeType !== ''
			? $mimeType
			: 'application/octet-stream';
	}
}

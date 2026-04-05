<?php

declare(strict_types=1);

namespace App\Application\Game;

use App\Data\Game\GameImageFileData;
use App\Data\Game\StoredGameImageData;
use App\Data\Game\UploadGameImageData;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Управляет хранением изображений библиотеки NPC текущего мастера.
 */
final class ActorImageStorageService
{
	private const string DISK_NAME = 'game_images';

	/**
	 * Возвращает список изображений библиотеки NPC текущего мастера.
	 *
	 * @return list<StoredGameImageData>
	 */
	public function getImages(User $user): array
	{
		$disk = $this->getDisk();
		$directory = $this->buildDirectory($user);
		$files = $disk->files($directory);
		sort($files);

		return array_map(
			fn (string $path): StoredGameImageData => $this->buildStoredImageData($path, $disk),
			$files,
		);
	}

	/**
	 * Сохраняет новое изображение в библиотеку NPC текущего мастера.
	 *
	 * @throws RuntimeException Если файл не удалось сохранить.
	 */
	public function storeImage(UploadGameImageData $data, User $user): StoredGameImageData
	{
		$disk = $this->getDisk();
		$directory = $this->buildDirectory($user);
		$extension = $data->file->guessExtension();

		if ($extension === null || $extension === '') {
			$extension = $data->file->getClientOriginalExtension();
		}

		if ($extension === '') {
			$extension = 'bin';
		}

		$fileName = Str::lower((string) Str::uuid()) . '.' . Str::lower($extension);
		$storedPath = $disk->putFileAs($directory, $data->file, $fileName);

		if (!is_string($storedPath) || $storedPath === '') {
			throw new RuntimeException('Не удалось сохранить изображение NPC.');
		}

		return $this->buildStoredImageData($storedPath, $disk, $data->file->getClientOriginalName());
	}

	/**
	 * Возвращает файл изображения NPC, если он принадлежит текущему мастеру.
	 */
	public function findImage(string $fileName, User $user): ?GameImageFileData
	{
		$safeFileName = basename($fileName);
		$path = $this->buildDirectory($user) . '/' . $safeFileName;
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
	 * Возвращает файл изображения NPC по имени без проверки владельца.
	 */
	public function findImageByFileName(string $fileName): ?GameImageFileData
	{
		$safeFileName = basename($fileName);
		$disk = $this->getDisk();

		foreach ($disk->allFiles('gm-actors') as $path) {
			if (basename($path) !== $safeFileName) {
				continue;
			}

			return new GameImageFileData(
				absolutePath: $disk->path($path),
				fileName: $safeFileName,
				mimeType: $this->resolveMimeType($disk, $path),
			);
		}

		return null;
	}

	/**
	 * Возвращает файловый диск для хранения изображений NPC.
	 */
	private function getDisk(): Filesystem
	{
		return Storage::disk(self::DISK_NAME);
	}

	/**
	 * Возвращает путь каталога библиотеки NPC на диске.
	 */
	private function buildDirectory(User $user): string
	{
		return 'gm-actors/' . $user->id;
	}

	/**
	 * Собирает DTO сохраненного изображения из файлового пути.
	 */
	private function buildStoredImageData(
		string $path,
		Filesystem $disk,
		?string $originalName = null,
	): StoredGameImageData
	{
		$fileName = basename($path);

		return new StoredGameImageData(
			fileName: $fileName,
			originalName: $originalName ?? $fileName,
			mimeType: $this->resolveMimeType($disk, $path),
			fileSize: $disk->size($path),
			downloadUrl: '/api/gm/actor-images/' . $fileName,
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

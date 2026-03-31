<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Auth\LocalDevelopmentUserService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Восстанавливает локальных dev-пользователей в базе данных.
 */
final class EnsureLocalUsersCommand extends Command
{
	/**
	 * Сигнатура artisan-команды.
	 *
	 * @var string
	 */
	protected $signature = 'app:ensure-local-users';

	/**
	 * Краткое описание artisan-команды.
	 *
	 * @var string
	 */
	protected $description = 'Создает или обновляет локальных пользователей для разработки';

	/**
	 * Создает консольную команду восстановления локальных пользователей.
	 */
	public function __construct(
		private readonly LocalDevelopmentUserService $localDevelopmentUserService,
	)
	{
		parent::__construct();
	}

	/**
	 * Выполняет восстановление локальных пользователей.
	 */
	public function handle(): int
	{
		try {
			$this->localDevelopmentUserService->ensureUsers();
		} catch (Throwable $throwable) {
			report($throwable);
			$this->error('Не удалось восстановить локальных пользователей.');

			return self::FAILURE;
		}

		$this->info('Локальные пользователи восстановлены.');

		return self::SUCCESS;
	}
}

<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Data\Auth\AuthSessionData;
use App\Data\Auth\ForgotPasswordData;
use App\Data\Auth\LoginCredentialsData;
use App\Data\Auth\RegisterUserData;
use App\Data\Auth\ResetPasswordData;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Управляет пользовательской сессией для JSON API авторизации.
 */
final readonly class AuthSessionService
{
	/**
	 * Создает сервис работы с пользовательской сессией.
	 */
	public function __construct(
		private AuthManager $authManager,
	)
	{
	}

	/**
	 * Возвращает снимок текущего состояния аутентификации.
	 */
	public function getSessionState(Request $request): AuthSessionData
	{
		/** @var User|null $user */
		$user = $request->user('web');

		return AuthSessionData::fromUser($user, $request->session()->token());
	}

	/**
	 * Регистрирует нового пользователя и сразу открывает сессию.
	 *
	 * @throws Throwable Если регистрация пользователя завершилась технической ошибкой.
	 */
	public function register(RegisterUserData $data, Request $request): AuthSessionData
	{
		/** @var User $user */
		$user = DB::transaction(static function () use ($data): User {
			return User::query()->create([
				'name' => $data->login,
				'email' => $data->email,
				'password' => Hash::make($data->password),
			]);
		});

		$guard = $this->authManager->guard('web');
		$guard->login($user);
		$request->session()->regenerate();

		return AuthSessionData::fromUser($user, $request->session()->token());
	}

	/**
	 * Выполняет вход пользователя по логину или email и паролю.
	 *
	 * @throws RuntimeException Если пользователь передал неверные учетные данные.
	 */
	public function login(LoginCredentialsData $credentials, Request $request): AuthSessionData
	{
		$guard = $this->authManager->guard('web');

		/** @var User|null $user */
		$user = User::query()
			->where('email', $credentials->login)
			->orWhere('name', $credentials->login)
			->first();

		if ($user === null || !Hash::check($credentials->password, (string)$user->getAuthPassword())) {
			throw new RuntimeException('Неверный логин, email или пароль.');
		}

		$guard->login($user, $credentials->remember);
		$request->session()->regenerate();

		return AuthSessionData::fromUser($user, $request->session()->token());
	}

	/**
	 * Завершает текущую пользовательскую сессию.
	 */
	public function logout(Request $request): void
	{
		$this->authManager->guard('web')->logout();

		$request->session()->invalidate();
		$request->session()->regenerateToken();
	}

	/**
	 * Отправляет письмо со ссылкой на восстановление пароля и возвращает нейтральное сообщение.
	 *
	 * @throws Throwable Если подсистема восстановления пароля завершилась технической ошибкой.
	 */
	public function requestPasswordReset(ForgotPasswordData $data): string
	{
		Password::sendResetLink([
			'email' => $data->email,
		]);

		return 'Если учетная запись существует, инструкции по восстановлению уже отправлены.';
	}

	/**
	 * Завершает сброс пароля по токену из письма.
	 *
	 * @throws RuntimeException Если токен сброса недействителен или устарел.
	 * @throws Throwable Если подсистема сброса пароля завершилась технической ошибкой.
	 */
	public function resetPassword(ResetPasswordData $data): void
	{
		$status = Password::reset(
			$data->toBrokerPayload(),
			function (User $user, string $password): void {
				$user->forceFill([
					'password' => Hash::make($password),
					'remember_token' => Str::random(60),
				])->save();
			},
		);

		if ($status !== Password::PASSWORD_RESET) {
			throw new RuntimeException('Ссылка для сброса пароля недействительна или устарела.');
		}
	}
}

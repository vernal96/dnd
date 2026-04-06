<?php

declare(strict_types=1);

use App\Domain\Actor\LuckScale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Добавляет показатель удачи к persistent и runtime-акторам.
	 */
	public function up(): void
	{
		Schema::table('actors', function (Blueprint $table): void {
			$table->string('luck', 16)->default(LuckScale::Normal->value)->after('health_max');
		});

		Schema::table('player_characters', function (Blueprint $table): void {
			$table->string('luck', 16)->default(LuckScale::Normal->value)->after('status');
		});

		Schema::table('actor_instances', function (Blueprint $table): void {
			$table->string('luck', 16)->default(LuckScale::Normal->value)->after('hp_max');
		});
	}

	/**
	 * Удаляет показатель удачи у persistent и runtime-актеров.
	 */
	public function down(): void
	{
		Schema::table('actor_instances', function (Blueprint $table): void {
			$table->dropColumn('luck');
		});

		Schema::table('player_characters', function (Blueprint $table): void {
			$table->dropColumn('luck');
		});

		Schema::table('actors', function (Blueprint $table): void {
			$table->dropColumn('luck');
		});
	}
};

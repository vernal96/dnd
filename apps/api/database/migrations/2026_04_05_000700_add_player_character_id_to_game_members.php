<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Добавляет выбранного персонажа игрока к участию в игре.
	 */
	public function up(): void
	{
		Schema::table('game_members', function (Blueprint $table): void {
			$table->foreignId('player_character_id')
				->nullable()
				->after('user_id')
				->constrained('player_characters')
				->cascadeOnUpdate()
				->nullOnDelete();
		});

		if (DB::getDriverName() === 'pgsql') {
			DB::statement(<<<'SQL'
				CREATE UNIQUE INDEX game_members_active_character_unique_idx
				ON game_members (player_character_id)
				WHERE player_character_id IS NOT NULL AND status = 'active'
			SQL);
		}
	}

	/**
	 * Удаляет связь выбранного персонажа игрока с участием в игре.
	 */
	public function down(): void
	{
		if (DB::getDriverName() === 'pgsql') {
			DB::statement('DROP INDEX IF EXISTS game_members_active_character_unique_idx');
		}

		Schema::table('game_members', function (Blueprint $table): void {
			$table->dropForeign(['player_character_id']);
			$table->dropColumn('player_character_id');
		});
	}
};

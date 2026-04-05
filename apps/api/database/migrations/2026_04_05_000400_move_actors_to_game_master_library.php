<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Переносит библиотеку акторов с уровня игры на уровень мастера.
	 */
	public function up(): void
	{
		Schema::table('actors', function (Blueprint $table): void {
			$table->foreignId('gm_user_id')->nullable()->after('id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->index(['gm_user_id', 'kind']);
			$table->index(['gm_user_id', 'name']);
		});

		DB::statement(<<<'SQL'
			UPDATE actors
			SET gm_user_id = games.gm_user_id
			FROM games
			WHERE actors.game_id = games.id
		SQL);

		Schema::table('actors', function (Blueprint $table): void {
			$table->dropIndex('actors_game_id_kind_index');
			$table->dropIndex('actors_game_id_name_index');
			$table->dropForeign(['game_id']);
			$table->dropColumn('game_id');
		});
	}

	/**
	 * Возвращает прежнюю привязку акторов к игре.
	 */
	public function down(): void
	{
		Schema::table('actors', function (Blueprint $table): void {
			$table->foreignId('game_id')->nullable()->after('gm_user_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
			$table->index(['game_id', 'kind']);
			$table->index(['game_id', 'name']);
		});

		DB::statement(<<<'SQL'
			UPDATE actors
			SET game_id = games.id
			FROM games
			WHERE actors.gm_user_id = games.gm_user_id
			  AND actors.game_id IS NULL
		SQL);

		Schema::table('actors', function (Blueprint $table): void {
			$table->dropIndex('actors_gm_user_id_kind_index');
			$table->dropIndex('actors_gm_user_id_name_index');
			$table->dropForeign(['gm_user_id']);
			$table->dropColumn('gm_user_id');
		});
	}
};

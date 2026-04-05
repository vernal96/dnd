<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Добавляет профильные поля героя игрока.
	 */
	public function up(): void
	{
		Schema::table('player_characters', function (Blueprint $table): void {
			$table->string('subrace', 64)->nullable()->after('race');
			$table->text('description')->nullable()->after('name');
			$table->string('image_path')->nullable()->after('derived_stats');
		});
	}

	/**
	 * Удаляет профильные поля героя игрока.
	 */
	public function down(): void
	{
		Schema::table('player_characters', function (Blueprint $table): void {
			$table->dropColumn([
				'subrace',
				'description',
				'image_path',
			]);
		});
	}
};

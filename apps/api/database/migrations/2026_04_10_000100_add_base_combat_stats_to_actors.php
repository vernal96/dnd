<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Добавляет базовые боевые показатели persistent-акторов.
	 */
	public function up(): void
	{
		Schema::table('actors', function (Blueprint $table): void {
			$table->unsignedInteger('armor_class')->default(10)->after('health_max');
			$table->unsignedInteger('jump_height')->default(1)->after('armor_class');
		});

		DB::table('actors')
			->whereNull('base_health')
			->update(['base_health' => 5]);
	}

	/**
	 * Откатывает базовые боевые показатели persistent-акторов.
	 */
	public function down(): void
	{
		Schema::table('actors', function (Blueprint $table): void {
			$table->dropColumn([
				'armor_class',
				'jump_height',
			]);
		});
	}
};

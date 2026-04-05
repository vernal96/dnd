<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Создает таблицу persistent-акторов внутри игр.
	 */
	public function up(): void
	{
		Schema::create('actors', function (Blueprint $table): void {
			$table->id();
			$table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
			$table->string('kind', 32)->default('npc')->index();
			$table->string('name');
			$table->text('description')->nullable();
			$table->unsignedSmallInteger('level')->default(1);
			$table->unsignedInteger('health_current')->nullable();
			$table->unsignedInteger('health_max')->nullable();
			$table->jsonb('stats')->nullable();
			$table->jsonb('inventory')->nullable();
			$table->string('image_path')->nullable();
			$table->jsonb('meta')->nullable();
			$table->timestamps();

			$table->index(['game_id', 'kind']);
			$table->index(['game_id', 'name']);
		});
	}

	/**
	 * Удаляет таблицу persistent-акторов.
	 */
	public function down(): void
	{
		Schema::dropIfExists('actors');
	}
};

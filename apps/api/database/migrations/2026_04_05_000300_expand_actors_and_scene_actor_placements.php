<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Расширяет акторов полями NPC и создает размещения актеров на сценах.
	 */
	public function up(): void
	{
		Schema::table('actors', function (Blueprint $table): void {
			$table->string('race', 64)->nullable()->after('description');
			$table->string('character_class', 64)->nullable()->after('race');
			$table->unsignedInteger('movement_speed')->default(6)->after('level');
			$table->unsignedInteger('base_health')->nullable()->after('movement_speed');
		});

		Schema::create('scene_actor_placements', function (Blueprint $table): void {
			$table->id();
			$table->foreignId('scene_template_id')->constrained('scene_templates')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreignId('actor_id')->constrained('actors')->cascadeOnUpdate()->cascadeOnDelete();
			$table->unsignedInteger('x');
			$table->unsignedInteger('y');
			$table->timestamps();

			$table->unique(['scene_template_id', 'actor_id']);
			$table->unique(['scene_template_id', 'x', 'y']);
		});
	}

	/**
	 * Откатывает расширение акторов и размещения актеров.
	 */
	public function down(): void
	{
		Schema::dropIfExists('scene_actor_placements');

		Schema::table('actors', function (Blueprint $table): void {
			$table->dropColumn([
				'race',
				'character_class',
				'movement_speed',
				'base_health',
			]);
		});
	}
};

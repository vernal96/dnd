<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создает первые доменные таблицы DnD, зафиксированные в проектной документации.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('gm_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 32)->default('draft')->index();
            $table->foreignId('active_scene_state_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->jsonb('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('game_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('role', 32)->default('player');
            $table->string('status', 32)->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'user_id']);
        });

        Schema::create('player_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('race', 64)->nullable();
            $table->string('class', 64)->nullable();
            $table->unsignedSmallInteger('level')->default(1);
            $table->unsignedBigInteger('experience')->default(0);
            $table->string('status', 32)->default('active');
            $table->jsonb('base_stats')->nullable();
            $table->jsonb('derived_stats')->nullable();
            $table->jsonb('unlocked_skills')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('character_progression', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_character_id')->constrained('player_characters')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('event_type', 64);
            $table->unsignedInteger('level_before')->nullable();
            $table->unsignedInteger('level_after')->nullable();
            $table->unsignedBigInteger('experience_before')->nullable();
            $table->unsignedBigInteger('experience_after')->nullable();
            $table->jsonb('payload')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['player_character_id', 'occurred_at']);
        });

        Schema::create('scene_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('status', 32)->default('draft')->index();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('scene_template_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_template_id')->constrained('scene_templates')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('x');
            $table->unsignedInteger('y');
            $table->string('terrain_type', 64);
            $table->integer('elevation')->default(0);
            $table->boolean('is_passable')->default(true);
            $table->boolean('blocks_vision')->default(false);
            $table->jsonb('props')->nullable();
            $table->timestamps();

            $table->unique(['scene_template_id', 'x', 'y']);
            $table->index(['scene_template_id', 'terrain_type']);
        });

        Schema::create('scene_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_template_id')->constrained('scene_templates')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('kind', 64);
            $table->string('name')->nullable();
            $table->unsignedInteger('x')->nullable();
            $table->unsignedInteger('y')->nullable();
            $table->unsignedInteger('width')->default(1);
            $table->unsignedInteger('height')->default(1);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_interactive')->default(false);
            $table->jsonb('state')->nullable();
            $table->jsonb('trigger_rules')->nullable();
            $table->timestamps();

            $table->index(['scene_template_id', 'kind']);
            $table->index(['scene_template_id', 'x', 'y']);
        });

        Schema::create('game_scene_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('scene_template_id')->constrained('scene_templates')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 32)->default('prepared')->index();
            $table->unsignedBigInteger('version')->default(1);
            $table->jsonb('grid_state')->nullable();
            $table->jsonb('objects_state')->nullable();
            $table->jsonb('visibility_state')->nullable();
            $table->jsonb('effects_state')->nullable();
            $table->jsonb('runtime_state')->nullable();
            $table->timestamp('loaded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['game_id', 'status']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->foreign('active_scene_state_id')
                ->references('id')
                ->on('game_scene_states')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('skill_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category', 64)->nullable();
            $table->text('description')->nullable();
            $table->jsonb('rules')->nullable();
            $table->jsonb('costs')->nullable();
            $table->timestamps();
        });

        Schema::create('item_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category', 64)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->jsonb('rules')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('actor_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_scene_state_id')->nullable()->constrained('game_scene_states')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('player_character_id')->nullable()->constrained('player_characters')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('controlled_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('kind', 32)->default('player_character');
            $table->string('controller_type', 32)->default('player');
            $table->string('name');
            $table->string('faction', 64)->nullable()->index();
            $table->string('social_state', 64)->nullable();
            $table->string('status', 32)->default('active')->index();
            $table->unsignedInteger('x')->nullable();
            $table->unsignedInteger('y')->nullable();
            $table->integer('initiative')->nullable();
            $table->unsignedInteger('hp_current')->nullable();
            $table->unsignedInteger('hp_max')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->jsonb('resources')->nullable();
            $table->jsonb('temporary_effects')->nullable();
            $table->jsonb('runtime_state')->nullable();
            $table->timestamps();

            $table->index(['game_id', 'player_character_id']);
            $table->index(['game_scene_state_id', 'x', 'y']);
        });

        Schema::create('item_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_template_id')->constrained('item_templates')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('game_id')->nullable()->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('owner_actor_id')->nullable()->constrained('actor_instances')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('game_scene_state_id')->nullable()->constrained('game_scene_states')->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('x')->nullable();
            $table->unsignedInteger('y')->nullable();
            $table->string('status', 32)->default('active');
            $table->jsonb('state')->nullable();
            $table->timestamps();

            $table->index(['owner_actor_id', 'status']);
            $table->index(['game_scene_state_id', 'x', 'y']);
        });

        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_scene_state_id')->constrained('game_scene_states')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status', 32)->default('preparing')->index();
            $table->unsignedInteger('round')->default(0);
            $table->foreignId('current_participant_id')->nullable();
            $table->string('trigger_type', 64)->nullable();
            $table->jsonb('payload')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['game_scene_state_id', 'status']);
        });

        Schema::create('encounter_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained('encounters')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('actor_instances')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('team_id', 64)->nullable();
            $table->integer('initiative')->nullable()->index();
            $table->unsignedInteger('turn_order')->nullable()->index();
            $table->unsignedInteger('joined_round')->default(1);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_surprised')->default(false);
            $table->unsignedInteger('movement_left')->default(0);
            $table->boolean('action_available')->default(true);
            $table->boolean('bonus_action_available')->default(true);
            $table->boolean('reaction_available')->default(true);
            $table->string('combat_result_state', 32)->default('active');
            $table->jsonb('state')->nullable();
            $table->timestamps();

            $table->unique(['encounter_id', 'actor_id']);
        });

        Schema::table('encounters', function (Blueprint $table) {
            $table->foreign('current_participant_id')
                ->references('id')
                ->on('encounter_participants')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('event_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->nullable()->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_scene_state_id')->nullable()->constrained('game_scene_states')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('actor_instances')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('event_type', 128)->index();
            $table->string('event_source', 64)->nullable();
            $table->unsignedBigInteger('aggregate_version')->nullable();
            $table->jsonb('payload')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['game_id', 'occurred_at']);
            $table->index(['game_scene_state_id', 'occurred_at']);
        });

        Schema::create('save_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('game_scene_state_id')->nullable()->constrained('game_scene_states')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('snapshot_type', 32)->default('manual');
            $table->string('status', 32)->default('ready')->index();
            $table->unsignedBigInteger('game_version')->nullable();
            $table->jsonb('state')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('saved_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['game_id', 'saved_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX actor_instances_player_character_single_game_idx
                ON actor_instances (player_character_id, game_id)
                WHERE player_character_id IS NOT NULL
            SQL);
        }
    }

    /**
     * Удаляет доменные таблицы DnD в обратном порядке зависимостей.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS actor_instances_player_character_single_game_idx');
        }

        Schema::table('encounters', function (Blueprint $table) {
            $table->dropForeign(['current_participant_id']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['active_scene_state_id']);
        });

        Schema::dropIfExists('save_snapshots');
        Schema::dropIfExists('event_log');
        Schema::dropIfExists('encounter_participants');
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('item_instances');
        Schema::dropIfExists('actor_instances');
        Schema::dropIfExists('item_templates');
        Schema::dropIfExists('skill_templates');
        Schema::dropIfExists('game_scene_states');
        Schema::dropIfExists('scene_objects');
        Schema::dropIfExists('scene_template_cells');
        Schema::dropIfExists('scene_templates');
        Schema::dropIfExists('character_progression');
        Schema::dropIfExists('player_characters');
        Schema::dropIfExists('game_members');
        Schema::dropIfExists('games');
    }
};

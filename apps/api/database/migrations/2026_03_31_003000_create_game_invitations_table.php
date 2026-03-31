<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создает таблицу приглашений пользователей в игровые столы.
     */
    public function up(): void
    {
        Schema::create('game_invitations', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('gm_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invited_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 120)->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['game_id', 'invited_user_id']);
            $table->index(['invited_user_id', 'status']);
        });
    }

    /**
     * Удаляет таблицу приглашений пользователей в игровые столы.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_invitations');
    }
};

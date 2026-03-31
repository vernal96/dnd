<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Добавляет флаг доступа к меню мастера в таблицу пользователей.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('can_access_gm')->default(false)->after('email');
        });
    }

    /**
     * Удаляет флаг доступа к меню мастера из таблицы пользователей.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('can_access_gm');
        });
    }
};

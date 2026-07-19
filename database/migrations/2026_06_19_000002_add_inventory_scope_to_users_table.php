<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'base_id')) {
                $table->unsignedBigInteger('base_id')
                    ->nullable()
                    ->after('password')
                    ->index();
            }

            if (! Schema::hasColumn('users', 'puede_ver_todos_inventarios')) {
                $table->boolean('puede_ver_todos_inventarios')
                    ->default(false)
                    ->after('base_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'base_id')) {
                $table->dropColumn('base_id');
            }

            if (Schema::hasColumn('users', 'puede_ver_todos_inventarios')) {
                $table->dropColumn('puede_ver_todos_inventarios');
            }
        });
    }
};

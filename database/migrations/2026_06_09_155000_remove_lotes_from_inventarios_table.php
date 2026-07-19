<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventarios') && Schema::hasColumn('inventarios', 'lotes_id')) {
            Schema::table('inventarios', function (Blueprint $table) {
                $table->dropForeign(['lotes_id']);
                $table->dropColumn('lotes_id');
            });
        }

        Schema::dropIfExists('lotes');
    }

    public function down(): void
    {
        if (! Schema::hasTable('lotes')) {
            Schema::create('lotes', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('inventarios') && ! Schema::hasColumn('inventarios', 'lotes_id')) {
            Schema::table('inventarios', function (Blueprint $table) {
                $table->foreignId('lotes_id')->nullable()->after('id')->constrained('lotes')->cascadeOnDelete();
            });
        }
    }
};

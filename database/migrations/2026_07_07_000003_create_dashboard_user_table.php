<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dashboard_user')) {
            Schema::create('dashboard_user', function (Blueprint $table) {
                $table->foreignId('dashboard_id')->constrained('dashboards')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->primary(['dashboard_id', 'user_id']);
            });
        }

        $now = now();
        DB::table('users')
            ->whereNotNull('dashboard_id')
            ->select(['id', 'dashboard_id'])
            ->orderBy('id')
            ->get()
            ->each(function ($user) use ($now): void {
                DB::table('dashboard_user')->updateOrInsert(
                    [
                        'dashboard_id' => $user->dashboard_id,
                        'user_id' => $user->id,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_user');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empleados')) {
            Schema::create('empleados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('deposito_id')->nullable()->constrained('depositos')->nullOnDelete();
                $table->string('nombres', 255);
                $table->string('apellidos', 255);
                $table->enum('tipo_doc', ['CI', 'DNI']);
                $table->string('numero_doc', 50)->unique();
                $table->string('telefono', 50)->nullable();
                $table->text('direccion')->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->timestamps();
            });

            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('empleados', 'usuario_id')) {
            foreach ($this->foreignKeyNamesForColumn('empleados', 'usuario_id') as $foreignKeyName) {
                DB::statement('ALTER TABLE empleados DROP FOREIGN KEY ' . $foreignKeyName);
            }

            DB::statement('ALTER TABLE empleados MODIFY usuario_id BIGINT UNSIGNED NULL');

            if (Schema::hasTable('users')) {
                DB::statement('UPDATE empleados LEFT JOIN users ON empleados.usuario_id = users.id SET empleados.usuario_id = NULL WHERE empleados.usuario_id IS NOT NULL AND users.id IS NULL');
                DB::statement('ALTER TABLE empleados ADD CONSTRAINT empleados_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL');
            }
        }

        if (Schema::hasColumn('empleados', 'deposito_id')) {
            DB::statement('ALTER TABLE empleados MODIFY deposito_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('empleados', 'telefono')) {
            DB::statement('ALTER TABLE empleados MODIFY telefono VARCHAR(50) NULL');
        }

        if (Schema::hasColumn('empleados', 'direccion')) {
            DB::statement('ALTER TABLE empleados MODIFY direccion TEXT NULL');
        }

        if (Schema::hasColumn('empleados', 'fecha_nacimiento')) {
            DB::statement('ALTER TABLE empleados MODIFY fecha_nacimiento DATE NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }

    private function foreignKeyNamesForColumn(string $table, string $column): array
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return [];
        }

        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME')
            ->all();
    }
};

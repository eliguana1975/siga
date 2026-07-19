<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('empleados', 'turno_laboral')) {
                $table->enum('turno_laboral', ['manana', 'tarde', 'noche'])->nullable()->after('tipo_empleado');
            }

            if (! Schema::hasColumn('empleados', 'es_franquero')) {
                $table->boolean('es_franquero')->default(false)->after('turno_laboral');
            }

            if (! Schema::hasColumn('empleados', 'franquero_de_tipo_empleado')) {
                $table->string('franquero_de_tipo_empleado', 100)->nullable()->after('es_franquero');
            }

            if (! Schema::hasColumn('empleados', 'franquero_de_empleado_id')) {
                $table->foreignId('franquero_de_empleado_id')->nullable()->after('franquero_de_tipo_empleado')->constrained('empleados')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'franquero_de_empleado_id')) {
                $table->dropConstrainedForeignId('franquero_de_empleado_id');
            }

            if (Schema::hasColumn('empleados', 'franquero_de_tipo_empleado')) {
                $table->dropColumn('franquero_de_tipo_empleado');
            }

            if (Schema::hasColumn('empleados', 'es_franquero')) {
                $table->dropColumn('es_franquero');
            }

            if (Schema::hasColumn('empleados', 'turno_laboral')) {
                $table->dropColumn('turno_laboral');
            }
        });
    }
};

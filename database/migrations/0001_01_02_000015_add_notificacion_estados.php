<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modificar la columna categoria para incluir 'notificacion'
        DB::statement("ALTER TABLE estados MODIFY categoria ENUM('general', 'membresia', 'pago', 'convenio', 'cliente', 'generico', 'notificacion')");

        // Insertar estados de notificaciones
        DB::table('estados')->insert([
            [
                'codigo' => 600,
                'nombre' => 'Pendiente',
                'descripcion' => 'Notificación programada pendiente de envío',
                'categoria' => 'notificacion',
                'color' => 'warning',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 601,
                'nombre' => 'Enviada',
                'descripcion' => 'Notificación enviada exitosamente',
                'categoria' => 'notificacion',
                'color' => 'success',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 602,
                'nombre' => 'Fallida',
                'descripcion' => 'Error al enviar la notificación',
                'categoria' => 'notificacion',
                'color' => 'danger',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 603,
                'nombre' => 'Cancelada',
                'descripcion' => 'Notificación cancelada manualmente',
                'categoria' => 'notificacion',
                'color' => 'secondary',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        // Eliminar estados de notificaciones
        DB::table('estados')->whereIn('codigo', [600, 601, 602, 603])->delete();
    }
};

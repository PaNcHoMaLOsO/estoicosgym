<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entradas por canje: el huésped del hotel que llega con su tarjeta y no paga.
 *
 * El convenio se marca como de canje, y cada entrada queda anotada con el
 * nombre de quien vino y el número de su tarjeta. No es un socio ni un pago:
 * no toca la caja ni las membresías.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->boolean('canje')->default(false)->after('activo');
        });

        Schema::create('entradas_canje', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('id_convenio')->constrained('convenios');
            $table->string('nombre', 100);
            $table->string('tarjeta', 50)->nullable();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['id_convenio', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entradas_canje');

        Schema::table('convenios', function (Blueprint $table) {
            $table->dropColumn('canje');
        });
    }
};

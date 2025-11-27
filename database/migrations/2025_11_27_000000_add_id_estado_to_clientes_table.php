<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedInteger('id_estado')->nullable()->after('activo')->comment('Rango 400-404: estados del cliente');
            $table->foreign('id_estado')->references('codigo')->on('estados')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['id_estado']);
            $table->dropColumn('id_estado');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->unsignedInteger('id_convenio')->nullable()->after('id_membresia')
                ->comment('Convenio aplicado al momento de la inscripción');
            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropForeign(['id_convenio']);
            $table->dropColumn('id_convenio');
        });
    }
};

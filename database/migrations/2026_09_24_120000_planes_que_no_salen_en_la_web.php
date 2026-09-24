<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hay planes que se venden en el mesón pero no se anuncian.
 *
 * LA WEB PUBLICABA TODO LO QUE SE PUEDE VENDER, y no es lo mismo. La Semana y
 * la Quincena son precios que se arreglan con cada persona: puestos en la
 * página, «Semana a $15.000» pasa a ser una promesa para cualquiera que
 * llegue, y el precio que se acordó con uno se vuelve el de todos.
 *
 * Apagarlos no servía: un plan apagado deja de poder venderse. Por eso va
 * aparte: «se puede vender» es una cosa y «sale en la web» es otra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membresias', function (Blueprint $tabla) {
            // Encendido por defecto: los planes de siempre se siguen viendo
            // igual que antes de que existiera esto.
            $tabla->boolean('en_la_web')->default(true)->after('activo');
        });

        // Los dos que el dueño señaló como precios personalizados.
        DB::table('membresias')->whereIn('nombre', ['Semana', 'Quincena'])->update(['en_la_web' => false]);
    }

    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $tabla) {
            $tabla->dropColumn('en_la_web');
        });
    }
};

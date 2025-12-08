<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/test-preview-directo/{id}', function($id) {
    // Mapeo de IDs a archivos
    $archivos = [
        1 => '01_bienvenida.html',
        2 => '02_pago_completado.html',
        3 => '03_membresia_por_vencer.html',
        4 => '04_membresia_vencida.html',
        5 => '05_pausa_inscripcion.html',
        6 => '06_activacion_inscripcion.html',
        7 => '07_pago_pendiente.html',
        8 => '08_renovacion.html',
        9 => '09_confirmacion_tutor_legal.html',
    ];
    
    $archivo = $archivos[$id] ?? null;
    
    if (!$archivo || !Storage::disk('local')->exists("test_emails/{$archivo}")) {
        return response('<h1>Plantilla no encontrada</h1>', 404);
    }
    
    // Cargar HTML
    $contenido = Storage::disk('local')->get("test_emails/{$archivo}");
    
    // Datos de ejemplo
    $datos = [
        'nombre' => 'Juan Pérez González',
        'nombre_cliente' => 'Juan Pérez González',
        'email_cliente' => 'juan.perez@ejemplo.cl',
        'run_cliente' => '12.345.678-9',
        'nombre_membresia' => 'Trimestral',
        'precio_membresia' => '$65.000',
        'fecha_inicio' => now()->format('d/m/Y'),
        'fecha_vencimiento' => now()->addMonths(3)->format('d/m/Y'),
        'tipo_pago' => 'Completo',
        'monto_pagado' => '$65.000',
        'monto_pendiente' => '$0',
        'monto_total' => '$65.000',
        'metodo_pago' => 'Transferencia Bancaria',
        'dias_restantes' => '7',
    ];
    
    // Reemplazar variables
    foreach ($datos as $variable => $valor) {
        $contenido = str_replace("{{$variable}}", $valor, $contenido);
    }
    
    return response($contenido)->header('Content-Type', 'text/html');
});

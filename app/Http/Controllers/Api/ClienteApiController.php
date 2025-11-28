<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Estado;
use Illuminate\Http\Request;

class ClienteApiController extends Controller
{
    /**
     * Listar todos los clientes activos
     */
    public function index()
    {
        $clientes = Cliente::where('activo', true)
            ->with(['inscripciones' => function($q) {
                $q->where('id_estado', Estado::where('nombre', 'Activa')->first()?->id ?? 100);
            }])
            ->get()
            ->map(function($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre_completo' => $cliente->nombres . ' ' . $cliente->apellido_paterno,
                    'run' => $cliente->run_pasaporte,
                    'email' => $cliente->email,
                    'celular' => $cliente->celular,
                    'inscripciones_activas' => $cliente->inscripciones->count(),
                ];
            });

        return response()->json($clientes);
    }

    /**
     * Obtener cliente específico con sus inscripciones
     */
    public function show($id)
    {
        $cliente = Cliente::with(['inscripciones' => function($q) {
            $q->with(['membresia', 'estado', 'pagos']);
        }, 'convenio'])->findOrFail($id);

        return response()->json([
            'id' => $cliente->id,
            'nombres' => $cliente->nombres,
            'apellido_paterno' => $cliente->apellido_paterno,
            'apellido_materno' => $cliente->apellido_materno,
            'run' => $cliente->run_pasaporte,
            'email' => $cliente->email,
            'celular' => $cliente->celular,
            'direccion' => $cliente->direccion,
            'fecha_nacimiento' => $cliente->fecha_nacimiento ? \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('Y-m-d') : null,
            'activo' => $cliente->activo,
            'convenio' => $cliente->convenio?->nombre,
            'inscripciones' => $cliente->inscripciones->map(function($ins) {
                $pagos = $ins->pagos ?? collect([]);
                $pagos_total = $pagos->sum('monto_abonado');
                $precio = $ins->precio_final ?? $ins->precio_base ?? 0;
                return [
                    'id' => $ins->id,
                    'membresia' => $ins->membresia?->nombre,
                    'estado' => $ins->estado?->nombre,
                    'fecha_inicio' => \Carbon\Carbon::parse($ins->fecha_inicio)->format('Y-m-d'),
                    'fecha_vencimiento' => \Carbon\Carbon::parse($ins->fecha_vencimiento)->format('Y-m-d'),
                    'precio_final' => $precio,
                    'pagos_total' => $pagos_total,
                    'pagos_pendientes' => $precio - $pagos_total,
                ];
            }),
        ]);
    }

    /**
     * Buscar clientes por nombre, RUN o email
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::where('activo', true)
            ->where(function($q) use ($query) {
                $q->where('nombres', 'like', '%' . $query . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $query . '%')
                    ->orWhere('run_pasaporte', 'like', '%' . $query . '%')
                    ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get()
            ->map(function($cliente) {
                return [
                    'id' => $cliente->id,
                    'text' => $cliente->nombres . ' ' . $cliente->apellido_paterno . ' (' . $cliente->run_pasaporte . ')',
                    'email' => $cliente->email,
                    'celular' => $cliente->celular,
                ];
            });

        return response()->json($clientes);
    }

    /**
     * Estadísticas del cliente
     */
    public function stats($id)
    {
        $cliente = Cliente::findOrFail($id);
        $inscripciones = $cliente->inscripciones;
        $pagos = Pago::whereIn('id_inscripcion', $inscripciones->pluck('id'))->get();

        return response()->json([
            'total_inscripciones' => $inscripciones->count(),
            'inscripciones_activas' => $inscripciones->where('id_estado', Estado::where('nombre', 'Activa')->first()?->id ?? 100)->count(),
            'total_pagado' => $pagos->sum('monto_abonado'),
            'cantidad_pagos' => $pagos->count(),
            'monto_promedio_pago' => $pagos->count() > 0 ? $pagos->sum('monto_abonado') / $pagos->count() : 0,
        ]);
    }

    /**
     * Validar RUT en tiempo real (AJAX)
     */
    public function validarRut(Request $request)
    {
        $rut = $request->input('rut');

        if (empty($rut)) {
            return response()->json([
                'valid' => false,
                'message' => 'El RUT no puede estar vacío'
            ]);
        }

        // Limpiar el RUT - eliminar espacios, puntos y guiones, convertir a mayúsculas
        $rutLimpio = preg_replace('/[\s\.\-]/', '', strtoupper($rut));
        $rutLimpio = preg_replace('/[^0-9K]/', '', $rutLimpio);

        // Validar longitud
        if (strlen($rutLimpio) < 8 || strlen($rutLimpio) > 9) {
            return response()->json([
                'valid' => false,
                'message' => 'El RUT debe tener 8-9 caracteres'
            ]);
        }

        // Separar el dígito verificador
        $dvExpected = substr($rutLimpio, -1);
        $rutNumber = substr($rutLimpio, 0, -1);

        // Calcular dígito verificador usando algoritmo módulo 11
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($rutNumber) - 1; $i >= 0; $i--) {
            $sum += intval($rutNumber[$i]) * $multiplier;
            $multiplier++;

            if ($multiplier > 7) {
                $multiplier = 2;
            }
        }

        $dv = 11 - ($sum % 11);

        if ($dv == 11) {
            $dvCalculated = '0';
        } elseif ($dv == 10) {
            $dvCalculated = 'K';
        } else {
            $dvCalculated = strval($dv);
        }

        if ($dvExpected !== $dvCalculated) {
            return response()->json([
                'valid' => false,
                'message' => 'El RUT no es válido. Verifica el dígito verificador.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'RUT válido',
            'rut_formateado' => $this->formatearRut($rutLimpio)
        ]);
    }

    /**
     * Formatear RUT a XX.XXX.XXX-X
     */
    private function formatearRut($rut)
    {
        $rut = str_pad($rut, 9, '0', STR_PAD_LEFT);
        
        $primera = substr($rut, 0, 2);
        $segunda = substr($rut, 2, 3);
        $tercera = substr($rut, 5, 3);
        $cuarta = substr($rut, 8, 1);

        return "{$primera}.{$segunda}.{$tercera}-{$cuarta}";
    }
}

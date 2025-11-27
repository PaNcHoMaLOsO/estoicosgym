<?php

namespace App\Http\Controllers\Api;

use App\Models\Pago;
use App\Models\Inscripcion;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PagoApiController extends Controller
{
    /**
     * POST /api/pagos
     * Registrar un pago simple o inicio de plan de cuotas
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_inscripcion' => 'required|exists:inscripciones,id',
            'monto_abonado' => 'required|numeric|min:0.01',
            'id_metodo_pago_principal' => 'required|exists:metodos_pago,id',
            'referencia_pago' => 'nullable|string|max:100',
            
            // Métodos múltiples (para pagos mixtos)
            'metodos_pago_json' => 'nullable|array',
            'metodos_pago_json.*' => 'numeric|min:0',
            
            // Cuotas (opcionales)
            'es_plan_cuotas' => 'boolean|nullable',
            'cantidad_cuotas' => 'required_if:es_plan_cuotas,true|nullable|integer|min:2|max:12',
            'fecha_vencimiento_cuota' => 'nullable|date|after:today',
            
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $inscripcion = Inscripcion::findOrFail($validated['id_inscripcion']);

            // VALIDACIÓN 1: Inscripción debe estar ACTIVA
            if ($inscripcion->id_estado != 1) {
                throw ValidationException::withMessages([
                    'error' => 'La inscripción no está activa',
                ]);
            }

            // VALIDACIÓN 2: No sobrepasar saldo pendiente
            $saldo = $inscripcion->getSaldoPendiente();
            if ($validated['monto_abonado'] > $saldo && !$validated['es_plan_cuotas']) {
                throw ValidationException::withMessages([
                    'error' => 'El monto excede el saldo pendiente',
                    'saldo_pendiente' => $saldo,
                ]);
            }

            if ($validated['es_plan_cuotas']) {
                // Crear plan de cuotas
                $pagos = $this->crearPlanCuotas($inscripcion, $validated);
                
                return response()->json([
                    'exito' => true,
                    'mensaje' => 'Plan de cuotas creado exitosamente',
                    'cuotas' => $pagos,
                    'cantidad_cuotas' => count($pagos),
                ], 201);
            } else {
                // Crear pago simple
                $pago = $this->crearPagoSimple($inscripcion, $validated);

                return response()->json([
                    'exito' => true,
                    'mensaje' => 'Pago registrado exitosamente',
                    'pago' => $pago->load(['inscripcion', 'metodoPagoPrincipal', 'estado']),
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'exito' => false,
                'errores' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear un pago simple (abono sin cuotas)
     */
    private function crearPagoSimple(Inscripcion $inscripcion, array $validated)
    {
        $pago = Pago::create([
            'id_inscripcion' => $inscripcion->id,
            'monto_abonado' => $validated['monto_abonado'],
            'monto_pendiente' => max(0, $inscripcion->getSaldoPendiente() - $validated['monto_abonado']),
            'id_metodo_pago_principal' => $validated['id_metodo_pago_principal'],
            'metodos_pago_json' => $validated['metodos_pago_json'] ?? null,
            'referencia_pago' => $validated['referencia_pago'] ?? null,
            'es_plan_cuotas' => false,
            'numero_cuota' => null,
            'cantidad_cuotas' => null,
            'fecha_vencimiento_cuota' => null,
            'grupo_pago' => null,
            'fecha_pago' => now()->format('Y-m-d'),
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        // Calcular y asignar estado dinámicamente
        $pago->id_estado = $pago->calculateEstadoDinamico();
        $pago->save();

        // Registrar en auditoría
        $this->registrarAuditoria('crear', 'pago', $pago->id, 'Pago simple registrado');

        return $pago;
    }

    /**
     * Crear un plan de cuotas
     */
    private function crearPlanCuotas(Inscripcion $inscripcion, array $validated)
    {
        $grupoPago = Str::uuid();
        $cantidadCuotas = $validated['cantidad_cuotas'];
        $montoTotal = $validated['monto_abonado'];
        $montoPorCuota = round($montoTotal / $cantidadCuotas, 2);

        $pagos = [];
        $fechaBase = now();

        for ($i = 1; $i <= $cantidadCuotas; $i++) {
            // Última cuota recibe el remanente para evitar decimales
            $montoEsta = ($i == $cantidadCuotas) 
                ? ($montoTotal - ($montoPorCuota * ($i - 1)))
                : $montoPorCuota;

            $fechaVencimiento = $fechaBase
                ->copy()
                ->addMonths($i)
                ->format('Y-m-d');

            $pago = Pago::create([
                'id_inscripcion' => $inscripcion->id,
                'monto_abonado' => $montoEsta,
                'monto_pendiente' => $montoEsta, // Inicialmente es pendiente
                'id_metodo_pago_principal' => $validated['id_metodo_pago_principal'],
                'referencia_pago' => $validated['referencia_pago'] ?? null,
                'es_plan_cuotas' => true,
                'numero_cuota' => $i,
                'cantidad_cuotas' => $cantidadCuotas,
                'monto_cuota' => $montoEsta,
                'fecha_vencimiento_cuota' => $fechaVencimiento,
                'grupo_pago' => $grupoPago,
                'fecha_pago' => now()->format('Y-m-d'),
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // Cuotas nuevas inician como PENDIENTE (101)
            $pago->id_estado = 101;
            $pago->save();

            $pagos[] = $pago;
        }

        // Registrar en auditoría
        $this->registrarAuditoria('crear', 'pago', implode(',', array_column($pagos, 'id')), 
            "Plan de {$cantidadCuotas} cuotas creado");

        return $pagos;
    }

    /**
     * GET /api/inscripciones/{id}/saldo
     * Obtener saldo pendiente de una inscripción
     * Devuelve datos en formato esperado por el formulario de pagos
     */
    public function getSaldo($id)
    {
        try {
            $inscripcion = Inscripcion::findOrFail($id);
            $precioFinal = $inscripcion->precio_final ?? $inscripcion->precio_base;

            return response()->json([
                'total_a_pagar' => $precioFinal,
                'total_abonado' => $inscripcion->getTotalAbonado(),
                'saldo_pendiente' => $inscripcion->getSaldoPendiente(),
                'porcentaje_pagado' => ($precioFinal > 0) 
                    ? round(($inscripcion->getTotalAbonado() / $precioFinal) * 100, 2) 
                    : 0,
                'estado' => $inscripcion->estaPagadaAlDia() ? 'Pagada' : 'Pendiente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Inscripción no encontrada',
            ], 404);
        }
    }

    /**
     * POST /api/pagos/calcular-cuotas
     * Simular cuotas sin crear
     * Útil para mostrar preview en frontend
     */
    public function calcularCuotas(Request $request)
    {
        $validated = $request->validate([
            'monto_total' => 'required|numeric|min:0.01',
            'cantidad_cuotas' => 'required|integer|min:2|max:12',
        ]);

        $montoPorCuota = round($validated['monto_total'] / $validated['cantidad_cuotas'], 2);
        $cuotas = [];
        $fechaBase = now();

        for ($i = 1; $i <= $validated['cantidad_cuotas']; $i++) {
            $montoEsta = ($i == $validated['cantidad_cuotas']) 
                ? ($validated['monto_total'] - ($montoPorCuota * ($i - 1)))
                : $montoPorCuota;

            $cuotas[] = [
                'numero' => $i,
                'monto' => $montoEsta,
                'vencimiento' => $fechaBase->copy()->addMonths($i)->format('Y-m-d'),
            ];
        }

        return response()->json([
            'exito' => true,
            'cantidad_cuotas' => $validated['cantidad_cuotas'],
            'monto_total' => $validated['monto_total'],
            'monto_por_cuota' => $montoPorCuota,
            'cuotas' => $cuotas,
        ]);
    }

    /**
     * GET /api/pagos/{id}
     * Obtener detalle de un pago
     */
    public function show($id)
    {
        try {
            $pago = Pago::with(['inscripcion', 'metodoPagoPrincipal', 'estado'])
                ->where('uuid', $id)
                ->orWhere('id', $id)
                ->firstOrFail();

            // Si es cuota, incluir cuotas relacionadas
            if ($pago->esParteDeCuotas()) {
                $pago->cuotasRelacionadas = $pago->cuotasRelacionadas();
            }

            return response()->json([
                'exito' => true,
                'pago' => $pago,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Pago no encontrado',
            ], 404);
        }
    }

    /**
     * PUT /api/pagos/{id}
     * Actualizar un pago
     */
    public function update(Request $request, $id)
    {
        try {
            $pago = Pago::findOrFail($id);

            $validated = $request->validate([
                'monto_abonado' => 'numeric|min:0.01',
                'id_metodo_pago_principal' => 'exists:metodos_pago,id',
                'referencia_pago' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string|max:500',
                'id_estado' => 'nullable|exists:estados,id',
            ]);

            // Actualizar campos permitidos
            $pago->update(array_filter($validated));

            // Recalcular estado si cambió monto abonado
            if (isset($validated['monto_abonado'])) {
                $pago->monto_pendiente = max(0, $pago->inscripcion->precio_final - $pago->monto_abonado);
                $pago->id_estado = $pago->calculateEstadoDinamico();
                $pago->save();
            }

            // Registrar auditoría
            $this->registrarAuditoria('actualizar', 'pago', $pago->id, 'Pago actualizado');

            return response()->json([
                'exito' => true,
                'mensaje' => 'Pago actualizado exitosamente',
                'pago' => $pago->load(['inscripcion', 'metodoPagoPrincipal']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/pagos/{id}
     * Eliminar un pago
     */
    public function destroy($id)
    {
        try {
            $pago = Pago::findOrFail($id);
            
            // No permitir eliminar si está pagado
            if ($pago->id_estado == 102) {
                throw new \Exception('No se puede eliminar un pago ya completado');
            }

            $pago->delete();

            // Registrar auditoría
            $this->registrarAuditoria('eliminar', 'pago', $id, 'Pago eliminado');

            return response()->json([
                'exito' => true,
                'mensaje' => 'Pago eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registrar acción en tabla de auditoría
     */
    private function registrarAuditoria($accion, $tabla, $registro_id, $descripcion)
    {
        try {
            Auditoria::create([
                'accion' => $accion,
                'tabla' => $tabla,
                'registro_id' => $registro_id,
                'descripcion' => $descripcion,
                'usuario' => auth()->user()->id ?? null,
                'ip' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Fallar silenciosamente en auditoría
            \Log::warning("Error registrando auditoría: {$e->getMessage()}");
        }
    }
}

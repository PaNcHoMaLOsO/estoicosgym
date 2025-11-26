<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Illuminate\Http\Request;

class PausaApiController extends Controller
{
    /**
     * Pausar una membresía
     * POST /api/pausas/{id_inscripcion}/pausar
     */
    public function pausar($id, Request $request)
    {
        $validated = $request->validate([
            'dias' => 'required|in:7,14,30|integer',
            'razon' => 'nullable|string|max:255',
        ]);

        try {
            $inscripcion = Inscripcion::with('cliente', 'estado')->findOrFail($id);

            // Verificar que pueda pausarse
            if (!$inscripcion->puedePausarse()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta membresía no puede ser pausada',
                    'info' => [
                        'pausada_actualmente' => $inscripcion->pausada,
                        'pausas_usadas' => $inscripcion->pausas_realizadas,
                        'pausas_disponibles' => $inscripcion->max_pausas_permitidas - $inscripcion->pausas_realizadas,
                        'estado' => $inscripcion->estado?->nombre,
                    ]
                ], 422);
            }

            $inscripcion->pausar($validated['dias'], $validated['razon'] ?? '');

            return response()->json([
                'success' => true,
                'message' => 'Membresía pausada exitosamente',
                'data' => [
                    'id' => $inscripcion->id,
                    'cliente' => $inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno,
                    'pausada' => $inscripcion->pausada,
                    'dias_pausa' => $inscripcion->dias_pausa,
                    'fecha_pausa_inicio' => $inscripcion->fecha_pausa_inicio?->format('d/m/Y'),
                    'fecha_pausa_fin' => $inscripcion->fecha_pausa_fin?->format('d/m/Y'),
                    'razon' => $inscripcion->razon_pausa,
                    'estado' => $inscripcion->estado?->nombre,
                    'pausas_usadas' => $inscripcion->pausas_realizadas,
                    'pausas_disponibles' => $inscripcion->max_pausas_permitidas - $inscripcion->pausas_realizadas,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Reanudar una membresía pausada
     * POST /api/pausas/{id_inscripcion}/reanudar
     */
    public function reanudar($id)
    {
        try {
            $inscripcion = Inscripcion::with('cliente', 'estado')->findOrFail($id);

            if (!$inscripcion->pausada) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta membresía no está pausada'
                ], 422);
            }

            $inscripcion->reanudar();

            return response()->json([
                'success' => true,
                'message' => 'Membresía reanudada exitosamente',
                'data' => [
                    'id' => $inscripcion->id,
                    'cliente' => $inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno,
                    'pausada' => $inscripcion->pausada,
                    'estado' => $inscripcion->estado?->nombre,
                    'fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('d/m/Y') ?? null,
                    'pausas_usadas' => $inscripcion->pausas_realizadas,
                    'pausas_disponibles' => $inscripcion->max_pausas_permitidas - $inscripcion->pausas_realizadas,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtener información de pausa
     * GET /api/pausas/{id_inscripcion}/info
     */
    public function info($id)
    {
        try {
            $inscripcion = Inscripcion::with('cliente', 'estado')->findOrFail($id);
            $infoPausa = $inscripcion->obtenerInfoPausa();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $inscripcion->id,
                    'cliente' => $inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno,
                    'puede_pausarse' => $inscripcion->puedepausarse(),
                    'pausa_info' => $infoPausa,
                    'estado' => $inscripcion->estado?->nombre,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Verificar pausas expiradas (ejecutable por cron)
     * POST /api/pausas/verificar-expiradas
     */
    public function verificarExpiradas()
    {
        $inscripcionesPausadas = Inscripcion::where('pausada', true)->with('cliente')->get();
        $reactivadas = 0;

        foreach ($inscripcionesPausadas as $inscripcion) {
            if ($inscripcion->verificarPausaExpirada()) {
                $reactivadas++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Verificación completada',
            'reactivadas' => $reactivadas,
            'total_pausadas' => $inscripcionesPausadas->count(),
        ]);
    }
}

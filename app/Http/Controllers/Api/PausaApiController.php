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
        $indefinida = $request->boolean('indefinida', false);
        
        $rules = [
            'razon' => 'nullable|string|max:500',
            'indefinida' => 'nullable|boolean',
        ];
        
        // Si no es indefinida, días es requerido
        if (!$indefinida) {
            $rules['dias'] = 'required|in:7,14,30';
        } else {
            // Para pausa indefinida, la razón es obligatoria
            $rules['razon'] = 'required|string|min:5|max:500';
        }

        $validated = $request->validate($rules);

        try {
            $inscripcion = Inscripcion::with('cliente', 'estado')->findOrFail($id);

            // Verificar que pueda pausarse
            if (!$inscripcion->puedeRealizarPausa()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta membresía no puede ser pausada',
                    'info' => [
                        'pausada_actualmente' => $inscripcion->pausada,
                        'pausas_usadas' => $inscripcion->pausas_realizadas,
                        'pausas_disponibles' => ($inscripcion->max_pausas_permitidas ?? 2) - ($inscripcion->pausas_realizadas ?? 0),
                        'estado' => $inscripcion->estado?->nombre,
                    ]
                ], 422);
            }

            $dias = $indefinida ? null : (int) $validated['dias'];
            $inscripcion->pausar($dias, $validated['razon'] ?? '', $indefinida);

            return response()->json([
                'success' => true,
                'message' => $indefinida ? 'Membresía pausada indefinidamente' : 'Membresía pausada exitosamente',
                'data' => [
                    'id' => $inscripcion->id,
                    'cliente' => $inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno,
                    'pausada' => $inscripcion->pausada,
                    'pausa_indefinida' => $inscripcion->pausa_indefinida,
                    'dias_pausa' => $inscripcion->dias_pausa,
                    'fecha_pausa_inicio' => $inscripcion->fecha_pausa_inicio?->format('d/m/Y'),
                    'fecha_pausa_fin' => $inscripcion->fecha_pausa_fin?->format('d/m/Y'),
                    'razon' => $inscripcion->razon_pausa,
                    'estado' => $inscripcion->estado?->nombre,
                    'pausas_usadas' => $inscripcion->pausas_realizadas,
                    'pausas_disponibles' => ($inscripcion->max_pausas_permitidas ?? 2) - $inscripcion->pausas_realizadas,
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

            // Obtener los días guardados antes de reanudar
            $diasRestantesGuardados = $inscripcion->dias_restantes_al_pausar ?? 0;
            
            // Calcular días que estuvo pausada para mostrar en la respuesta
            $diasEnPausa = $inscripcion->fecha_pausa_inicio 
                ? $inscripcion->fecha_pausa_inicio->diffInDays(now()) 
                : 0;

            $inscripcion->reanudar();

            return response()->json([
                'success' => true,
                'message' => "Membresía reanudada. Se restauraron {$diasRestantesGuardados} días de membresía.",
                'data' => [
                    'id' => $inscripcion->id,
                    'cliente' => $inscripcion->cliente->nombres . ' ' . $inscripcion->cliente->apellido_paterno,
                    'pausada' => $inscripcion->pausada,
                    'estado' => $inscripcion->estado?->nombre,
                    'fecha_vencimiento' => $inscripcion->fecha_vencimiento?->format('d/m/Y') ?? null,
                    'dias_en_pausa' => $diasEnPausa,
                    'dias_restaurados' => $diasRestantesGuardados,
                    'pausas_usadas' => $inscripcion->pausas_realizadas,
                    'pausas_disponibles' => ($inscripcion->max_pausas_permitidas ?? 2) - $inscripcion->pausas_realizadas,
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
                    'puede_pausarse' => $inscripcion->puedePausarse(),
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

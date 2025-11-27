<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Inscripcion;
use Illuminate\Http\Request;

class SearchApiController extends Controller
{
    /**
     * Buscar clientes por nombre, email o RUT
     * GET /api/clientes/search?q=term
     */
    public function searchClientes(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::where('activo', true)
            ->where(function ($q) use ($query) {
                $q->where('nombres', 'LIKE', "%{$query}%")
                  ->orWhere('apellido_paterno', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('run_pasaporte', 'LIKE', "%{$query}%");
            })
            ->limit(20)
            ->get(['id', 'nombres', 'apellido_paterno', 'email']);

        return response()->json(
            $clientes->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'text' => "{$cliente->nombres} {$cliente->apellido_paterno} ({$cliente->email})",
                ];
            })
        );
    }

    /**
     * Buscar inscripciones por cliente o estado
     * GET /api/inscripciones/search?q=term
     */
    public function searchInscripciones(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $inscripciones = Inscripcion::with(['cliente', 'estado'])
            ->where(function ($q) use ($query) {
                $q->whereHas('cliente', function ($clienteQuery) use ($query) {
                    $clienteQuery->where('nombres', 'LIKE', "%{$query}%")
                                 ->orWhere('apellido_paterno', 'LIKE', "%{$query}%")
                                 ->orWhere('email', 'LIKE', "%{$query}%");
                })->orWhereHas('estado', function ($estadoQuery) use ($query) {
                    $estadoQuery->where('nombre', 'LIKE', "%{$query}%");
                })->orWhere('id', $query);
            })
            ->limit(20)
            ->get(['id', 'id_cliente', 'id_estado']);

        return response()->json(
            $inscripciones->map(function ($inscripcion) {
                return [
                    'id' => $inscripcion->id,
                    'text' => "#{$inscripcion->id} - {$inscripcion->cliente->nombres} {$inscripcion->cliente->apellido_paterno} ({$inscripcion->estado->nombre})",
                ];
            })
        );
    }
}

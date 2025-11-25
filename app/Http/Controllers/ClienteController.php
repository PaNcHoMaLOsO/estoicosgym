<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Inscripcion;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\Estado;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(): View
    {
        $clientes = Cliente::with('convenio', 'inscripciones')
            ->where('activo', true)
            ->paginate(15);
        
        return view('clientes.index', compact('clientes'));
    }

    public function create(): View
    {
        $convenios = \App\Models\Convenio::where('activo', true)->get();
        
        return view('clientes.create', compact('convenios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'run_pasaporte' => 'nullable|string|unique:clientes',
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'celular' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'id_convenio' => 'nullable|integer|exists:convenios,id',
            'observaciones' => 'nullable|string',
        ]);

        Cliente::create($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load('convenio', 'inscripciones.membresia', 'inscripciones.estado', 'pagos');
        
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente): View
    {
        $convenios = \App\Models\Convenio::where('activo', true)->get();
        
        return view('clientes.edit', compact('cliente', 'convenios'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'run_pasaporte' => 'nullable|string|unique:clientes,run_pasaporte,' . $cliente->id,
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'celular' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'id_convenio' => 'nullable|integer|exists:convenios,id',
            'observaciones' => 'nullable|string',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);

        return redirect()->route('clientes.index')->with('success', 'Cliente desactivado exitosamente.');
    }
}

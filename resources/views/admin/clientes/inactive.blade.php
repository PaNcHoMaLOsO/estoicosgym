@extends('adminlte::page')

@section('title', 'Clientes Desactivados - EstóicosGym')

@section('css')
    <style>
        .inactive-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .restore-btn {
            transition: all 0.3s ease;
        }

        .restore-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
@endsection

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-user-slash"></i> Clientes Desactivados
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Activos
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fas fa-check-circle"></i> {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-info">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Registro de Clientes Desactivados
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">Total: {{ $clientes->total() }}</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            @if($clientes->count() > 0)
                <table class="table table-hover table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10%">ID</th>
                            <th style="width: 25%">Nombre</th>
                            <th style="width: 20%">Email</th>
                            <th style="width: 15%">Celular</th>
                            <th style="width: 15%">Desactivado</th>
                            <th style="width: 15%; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                            <tr>
                                <td>
                                    <span class="badge badge-secondary">{{ $cliente->id }}</span>
                                </td>
                                <td>
                                    <strong>{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</strong>
                                    @if($cliente->apellido_materno)
                                        <br><small class="text-muted">{{ $cliente->apellido_materno }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="mailto:{{ $cliente->email }}" class="text-primary">{{ $cliente->email }}</a>
                                </td>
                                <td>
                                    @if($cliente->celular)
                                        <a href="tel:{{ $cliente->celular }}" class="text-success">{{ $cliente->celular }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $cliente->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.clientes.show', $cliente) }}" 
                                           class="btn btn-info restore-btn" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <button type="button" 
                                                class="btn btn-success restore-btn" 
                                                title="Reactivar cliente"
                                                onclick="confirmarReactivacion('{{ $cliente->uuid }}', '{{ $cliente->nombres }}')">
                                            <i class="fas fa-undo"></i> Reactivar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-success">No hay clientes desactivados</h5>
                    <p class="text-muted">Todos tus clientes están activos en el sistema</p>
                </div>
            @endif
        </div>
        @if($clientes->hasPages())
            <div class="card-footer">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para Reactivar Cliente -->
    <div class="modal fade" id="reactivarClienteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-undo"></i> Reactivar Cliente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Deseas reactivar a <strong id="nombreCliente"></strong>?</p>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            El cliente volverá a aparecer en el listado de clientes activos.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <form id="formReactivar" method="POST" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Sí, Reactivar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script>
        function confirmarReactivacion(clienteId, nombre) {
            document.getElementById('nombreCliente').textContent = nombre;
            // Usar la ruta helper de Laravel en lugar de construir URL manualmente
            const actionUrl = "{{ route('admin.clientes.reactivate', ':id') }}".replace(':id', clienteId);
            document.getElementById('formReactivar').action = actionUrl;
            $('#reactivarClienteModal').modal('show');
        }
    </script>
@endsection

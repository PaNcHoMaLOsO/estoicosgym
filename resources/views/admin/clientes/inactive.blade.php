@extends('adminlte::page')

@section('title', 'Clientes Desactivados - EstóicosGym')

@section('css')
    <style>
        /* ===== STATS ===== */
        .stat-card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            color: #495057;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== TABLE ===== */
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            border-bottom: 2px solid #667eea;
        }

        .table thead th {
            color: #2c3e50;
            font-weight: 700;
            padding: 1.25rem 1rem;
            border: 0;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            box-shadow: inset 0 0 5px rgba(102, 126, 234, 0.1);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        .client-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .client-rut {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 600;
        }

        .client-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* ===== ACTIONS ===== */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 0.5rem;
            border: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-view {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-restore {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        /* ===== CARD ===== */
        .card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
        }

        .card-header {
            border-bottom: 2px solid #667eea;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .card-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #11998e;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            color: #11998e;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #6c757d;
        }

        /* ===== ALERTS ===== */
        .alert-success-custom {
            background: linear-gradient(135deg, #f0fff4 0%, #e8f8f0 100%);
            border: 2px solid #11998e;
            border-radius: 0.75rem;
            padding: 1rem;
            color: #155724;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .stat-number {
                font-size: 1.8em;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
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
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-lg" role="alert" style="border-left: 5px solid #28a745;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-check-circle"></i> ¡Éxito!
            </h5>
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-lg" role="alert" style="border-left: 5px solid #dc3545;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="alert-heading mb-2">
                <i class="fas fa-exclamation-circle"></i> Error
            </h5>
            {{ $message }}
        </div>
    @endif

    <!-- ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-number">{{ $clientes->total() }}</div>
                    <div class="stat-label">Total Desactivados</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-number">{{ round((($clientes->total() / (\App\Models\Cliente::count() ?: 1)) * 100), 1) }}%</div>
                    <div class="stat-label">Porcentaje del Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE CLIENTES DESACTIVADOS -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="flex-grow: 1;">
                <i class="fas fa-list"></i> Registro de Clientes Desactivados
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">Total: {{ $clientes->total() }}</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            @if($clientes->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 8%;">ID</th>
                            <th style="width: 20%;">RUT / Nombre</th>
                            <th style="width: 25%;">Email</th>
                            <th style="width: 15%;">Celular</th>
                            <th style="width: 15%;">Desactivado</th>
                            <th style="width: 17%; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                            <tr>
                                <td>
                                    <span class="badge badge-secondary">#{{ $cliente->id }}</span>
                                </td>
                                <td>
                                    <div class="client-rut">{{ $cliente->run_pasaporte }}</div>
                                    <div class="client-name">
                                        {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                                        @if($cliente->apellido_materno)
                                            {{ $cliente->apellido_materno }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $cliente->email }}" class="text-primary">
                                        {{ $cliente->email }}
                                    </a>
                                </td>
                                <td>
                                    @if($cliente->celular)
                                        <a href="tel:{{ $cliente->celular }}" class="text-primary">
                                            {{ $cliente->celular }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="client-date">
                                        <i class="fas fa-calendar-times"></i> {{ $cliente->updated_at->format('d/m/Y') }}
                                        <br>
                                        <small>{{ $cliente->updated_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.clientes.show', $cliente) }}" 
                                           class="btn-action btn-view" title="Ver detalles">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <button type="button" 
                                                class="btn-action btn-restore" 
                                                title="Reactivar cliente"
                                                onclick="confirmarReactivacion('{{ $cliente->uuid }}', '{{ addslashes($cliente->nombres) }} {{ addslashes($cliente->apellido_paterno) }}')">
                                            <i class="fas fa-undo"></i> Reactivar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5>No hay clientes desactivados</h5>
                    <p>Todos tus clientes están activos en el sistema</p>
                </div>
            @endif
        </div>
        @if($clientes->hasPages())
            <div class="card-footer">
                <nav aria-label="Page navigation">
                    <div class="d-flex justify-content-center">
                        {{ $clientes->links('pagination::bootstrap-4') }}
                    </div>
                </nav>
            </div>
        @endif
    </div>

    <!-- FORM REACTIVAR (OCULTO - USADO POR SWEETALERT2) -->
    <form id="formReactivar" method="POST" style="display:none;">
        @csrf
        @method('PATCH')
    </form>

@endsection

@push('js')
    {{-- Cargar SweetAlert2 por si no está en el layout --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmarReactivacion(clienteId, nombre) {
            console.log('confirmarReactivacion llamado:', clienteId, nombre);
            
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 no está disponible');
                if (confirm('¿Deseas reactivar a ' + nombre + '?')) {
                    const actionUrl = "{{ url('admin/clientes') }}/" + clienteId + "/reactivar";
                    document.getElementById('formReactivar').action = actionUrl;
                    document.getElementById('formReactivar').submit();
                }
                return;
            }
            
            Swal.fire({
                title: '¿Reactivar Cliente?',
                html: `
                    <div style="text-align: left;">
                        <p style="margin-bottom: 1rem;">Estás a punto de <strong>reactivar a ${nombre}</strong>.</p>
                        <div style="background: linear-gradient(135deg, #f0fff4 0%, #e8f8f0 100%); border: 2px solid #11998e; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
                            <h6 style="color: #155724; font-weight: 700; margin: 0 0 0.5rem 0;">¿Qué sucede al reactivar?</h6>
                            <ul style="color: #495057; margin: 0; padding-left: 1.5rem;">
                                <li><i class="fas fa-check text-success"></i> El cliente volverá al estado <strong>ACTIVO</strong></li>
                                <li><i class="fas fa-check text-success"></i> Aparecerá en el <strong>listado principal</strong></li>
                                <li><i class="fas fa-check text-success"></i> Todo su <strong>historial se preserva</strong></li>
                                <li><i class="fas fa-check text-success"></i> Podrá crear <strong>nuevas inscripciones</strong></li>
                            </ul>
                        </div>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-undo"></i> Sí, Reactivar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                confirmButtonColor: '#11998e',
                cancelButtonColor: '#6c757d',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Construir URL correctamente
                    const actionUrl = "{{ url('admin/clientes') }}/" + clienteId + "/reactivar";
                    console.log('URL de reactivación:', actionUrl);
                    document.getElementById('formReactivar').action = actionUrl;
                    
                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando...',
                        html: '<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            document.getElementById('formReactivar').submit();
                        }
                    });
                }
            });
        }
    </script>
@endpush

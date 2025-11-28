@extends('adminlte::page')

@section('title', 'Clientes - EstóicosGym')

@section('css')
    <style>
        /* ===== CARDS ESTADÍSTICAS ===== */
        .stat-card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
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

        /* ===== BUSCADOR ===== */
        .search-box {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .search-box input {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .filter-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-badge:hover {
            transform: scale(1.05);
        }

        .filter-badge.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        /* ===== TABLA ===== */
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

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .status-inactive {
            background: #e9ecef;
            color: #6c757d;
        }

        /* ===== ACCIONES ===== */
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

        .btn-edit {
            background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            color: white;
        }

        /* ===== CARD HEADER ===== */
        .card-header {
            border-bottom: 2px solid #667eea;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .card {
            border: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.85rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }

            .stat-number {
                font-size: 1.8em;
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
@stop

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-users"></i> Gestión de Clientes
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.clientes.inactive') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-user-slash"></i> Ver Desactivados
            </a>
            <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Cliente
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

    <!-- ESTADÍSTICAS RÁPIDAS -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-number">{{ $clientes->total() }}</div>
                    <div class="stat-label">Total de Clientes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-number">{{ $clientes->where('activo', true)->count() }}</div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-number">{{ $clientes->where('activo', false)->count() }}</div>
                    <div class="stat-label">Inactivos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-number">{{ round(($clientes->where('activo', true)->count() / max($clientes->total(), 1)) * 100) }}%</div>
                    <div class="stat-label">Tasa Actividad</div>
                </div>
            </div>
        </div>
    </div>

    <!-- BUSCADOR Y FILTROS -->
    <div class="search-box">
        <div class="row">
            <div class="col-md-8">
                <input type="text" class="form-control form-control-lg" id="searchInput" placeholder="🔍 Buscar por nombre, RUT, email o celular...">
            </div>
            <div class="col-md-4">
                <select class="form-control form-control-lg" id="filterStatus">
                    <option value="">-- Todos los Estados --</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABLA DE CLIENTES -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Listado de Clientes
            </h3>
            <div class="card-tools">
                <span class="badge badge-light" id="resultCount">Total: {{ $clientes->count() }}</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 20%;">RUT / Nombre</th>
                        <th style="width: 25%;">Email</th>
                        <th style="width: 15%;">Celular</th>
                        <th style="width: 12%;">Estado</th>
                        <th style="width: 20%; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="clientesTableBody">
                    @forelse($clientes as $cliente)
                        <tr class="cliente-row" data-cliente="{{ json_encode($cliente) }}">
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
                                <a href="tel:{{ $cliente->celular }}" class="text-primary">
                                    {{ $cliente->celular }}
                                </a>
                            </td>
                            <td>
                                <span class="status-badge {{ $cliente->activo ? 'status-active' : 'status-inactive' }}">
                                    {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn-action btn-view" title="Ver detalles">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete" title="Eliminar">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox"></i> No hay clientes registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <div class="d-flex justify-content-center">
                    {{ $clientes->links('pagination::bootstrap-4') }}
                </div>
            </nav>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const statusFilter = document.getElementById('filterStatus').value;
            let visibleCount = 0;
            
            document.querySelectorAll('.cliente-row').forEach(row => {
                const cliente = JSON.parse(row.getAttribute('data-cliente'));
                const fullName = `${cliente.nombres} ${cliente.apellido_paterno} ${cliente.apellido_materno || ''}`.toLowerCase();
                const rut = cliente.run_pasaporte.toLowerCase();
                const email = cliente.email.toLowerCase();
                const celular = cliente.celular.toLowerCase();
                const estado = cliente.activo ? 'activo' : 'inactivo';
                
                const matchesSearch = fullName.includes(searchTerm) || rut.includes(searchTerm) || 
                                     email.includes(searchTerm) || celular.includes(searchTerm);
                const matchesStatus = !statusFilter || estado === statusFilter;
                
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('resultCount').textContent = `Resultados: ${visibleCount}`;
        });

        // Filtro por estado
        document.getElementById('filterStatus').addEventListener('change', function() {
            document.getElementById('searchInput').dispatchEvent(new Event('keyup'));
        });

        // Confirmación de eliminación con SweetAlert2
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                e.preventDefault();
                
                const form = e.target.closest('form');
                const clienteNombre = e.target.closest('tr').querySelector('strong')?.textContent || 'este cliente';
                
                Swal.fire({
                    title: '¿Eliminar Cliente?',
                    html: `<div style="text-align: left; font-size: 0.95em; color: #555;">
                        <p style="margin: 15px 0;"><i class="fas fa-exclamation-triangle" style="color: #ff6b6b; margin-right: 8px;"></i>Vas a eliminar: <strong>${clienteNombre}</strong></p>
                        <p style="margin: 10px 0; font-size: 0.9em; color: #999;">Esta acción es irreversible. Perderás todos sus datos.</p>
                    </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'swal2-popup-custom'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loading
                        Swal.fire({
                            title: 'Eliminando...',
                            html: '<i class="fas fa-spinner fa-spin" style="font-size: 2em; color: #667eea;"></i>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                // Enviar el formulario
                                form.submit();
                            }
                        });
                    }
                });
            }
        });
    </script>
@endpush

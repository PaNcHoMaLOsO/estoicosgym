@extends('adminlte::page')

@section('title', 'Inscripciones - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Gestión de Inscripciones</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('admin.inscripciones.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Nueva Inscripción
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Éxito!</strong> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card card-primary collapsed-card">
        <div class="card-header with-border">
            <h3 class="card-title">Filtros</h3>
            <div class="card-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display:none;">
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control ml-2">
                        <option value="">-- Todos --</option>
                        <option value="1">Activa</option>
                        <option value="2">Próxima a Vencer</option>
                        <option value="3">Vencida</option>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="cliente">Cliente:</label>
                    <input type="text" id="cliente" name="cliente" class="form-control ml-2" placeholder="Buscar cliente...">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Inscripciones</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Pausa</th>
                        <th>Monto</th>
                        <th>Abonado</th>
                        <th>Pendiente</th>
                        <th>Inicio</th>
                        <th>Vencimiento</th>
                        <th>Días Restantes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inscripciones as $inscripcion)
                        <tr>
                            <td>{{ $inscripcion->id }}</td>
                            <td>
                                <strong>{{ $inscripcion->cliente->nombres }} {{ $inscripcion->cliente->apellido_paterno }}</strong>
                            </td>
                            <td>
                                {!! \App\Helpers\EstadoHelper::badgeWithIcon($inscripcion->estado) !!}
                            </td>
                            <td>
                                @if($inscripcion->pausada)
                                    <span class="badge bg-warning"><i class="fas fa-pause-circle"></i> {{ $inscripcion->dias_pausa }}d</span>
                                @else
                                    <span class="badge bg-success">Activo</span>
                                @endif
                            </td>
                            <td>
                                <strong>${{ number_format($inscripcion->precio_final ?? $inscripcion->precio_base, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                    $abonado = $inscripcion->pagos()->where('id_estado', 102)->sum('monto_abonado');
                                @endphp
                                <span class="text-success"><strong>${{ number_format($abonado, 2) }}</strong></span>
                            </td>
                            <td>
                                @php
                                    $monto_total = $inscripcion->precio_final ?? $inscripcion->precio_base;
                                    $pendiente = $monto_total - $abonado;
                                @endphp
                                @if($pendiente > 0)
                                    <span class="text-danger"><strong>${{ number_format($pendiente, 2) }}</strong></span>
                                @else
                                    <span class="text-success badge badge-success">Pagado</span>
                                @endif
                            </td>
                            <td>{{ $inscripcion->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $inscripcion->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $diasRestantes = (int) now()->diffInDays($inscripcion->fecha_vencimiento, false);
                                @endphp
                                @if($diasRestantes > 7)
                                    <span class="badge bg-info">{{ $diasRestantes }} días</span>
                                @elseif($diasRestantes > 0)
                                    <span class="badge bg-warning">{{ $diasRestantes }} días</span>
                                @else
                                    <span class="badge bg-danger">Vencida</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inscripciones.edit', $inscripcion) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.inscripciones.destroy', $inscripcion) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No hay inscripciones registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $inscripciones->links() }}
        </div>
    </div>
@stop

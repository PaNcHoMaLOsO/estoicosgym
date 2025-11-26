@extends('adminlte::page')

@section('title', 'Membresías Desactivadas - EstóicosGym')

@section('content_header')
    <div class="row mb-4">
        <div class="col-sm-8">
            <h1 class="m-0">
                <i class="fas fa-ban"></i> Membresías Desactivadas
            </h1>
        </div>
        <div class="col-sm-4 text-right">
            <a href="{{ route('admin.membresias.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Activas
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Listado de Membresías Desactivadas
            </h3>
            <div class="card-tools">
                <span class="badge badge-warning">{{ $membresias->total() }}</span>
            </div>
        </div>
        <div class="card-body table-responsive">
            @if ($membresias->count() > 0)
                <table class="table table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 8%;">ID</th>
                            <th style="width: 20%;">Nombre</th>
                            <th style="width: 12%;">Duración</th>
                            <th style="width: 12%;">Precio Actual</th>
                            <th style="width: 10%;">Inscripciones Activas</th>
                            <th style="width: 20%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($membresias as $membresia)
                            <tr>
                                <td>
                                    <small class="badge badge-secondary">{{ substr($membresia->id, 0, 8) }}...</small>
                                </td>
                                <td>
                                    <strong>{{ $membresia->nombre }}</strong>
                                    @if ($membresia->descripcion)
                                        <br><small class="text-muted">{{ Str::limit($membresia->descripcion, 60) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($membresia->duracion_meses > 0)
                                        <span class="badge badge-info">{{ $membresia->duracion_meses }} mes(es)</span>
                                    @else
                                        <span class="badge badge-success">{{ $membresia->duracion_dias }} día(s)</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $precioActual = $membresia->precios()->latest('created_at')->first();
                                    @endphp
                                    @if ($precioActual)
                                        <strong>${{ number_format($precioActual->precio_normal, 0, '.', '.') }}</strong>
                                        @if ($precioActual->precio_convenio)
                                            <br><small class="text-success">
                                                Con conv: ${{ number_format($precioActual->precio_convenio, 0, '.', '.') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $inscripcionesActivas = $membresia->inscripciones()
                                            ->whereNotIn('id_estado', [3, 5])
                                            ->count();
                                    @endphp
                                    @if ($inscripcionesActivas > 0)
                                        <span class="badge badge-danger">{{ $inscripcionesActivas }}</span>
                                    @else
                                        <span class="badge badge-success">Ninguna</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.membresias.show', $membresia) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        
                                        <!-- Botón Reactivar -->
                                        <form action="{{ route('admin.membresias.update', $membresia) }}" method="POST" style="display:inline;" class="form-reactivar">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="activo" value="1">
                                            <input type="hidden" name="nombre" value="{{ $membresia->nombre }}">
                                            <input type="hidden" name="duracion_meses" value="{{ $membresia->duracion_meses }}">
                                            <input type="hidden" name="duracion_dias" value="{{ $membresia->duracion_dias }}">
                                            @php
                                                $precioActual = $membresia->precios()->latest('created_at')->first();
                                            @endphp
                                            @if ($precioActual)
                                                <input type="hidden" name="precio_normal" value="{{ $precioActual->precio_normal }}">
                                            @endif
                                            <button type="submit" class="btn btn-sm btn-success" title="Reactivar membresía">
                                                <i class="fas fa-redo"></i> Reactivar
                                            </button>
                                        </form>
                                        
                                        <!-- Botón Eliminar Permanentemente -->
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminacion('{{ $membresia->id }}', '{{ $membresia->nombre }}', {{ $inscripcionesActivas }});" title="Eliminar permanentemente">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $membresias->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>¡Excelente!</strong> No hay membresías desactivadas en el sistema.
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
function confirmarEliminacion(id, nombre, inscripcionesActivas) {
    let mensaje = `¿Estás seguro de que deseas eliminar permanentemente la membresía "${nombre}"?`;
    
    if (inscripcionesActivas > 0) {
        mensaje += `\n\n⚠️ ADVERTENCIA: Esta membresía tiene ${inscripcionesActivas} inscripción(es) activa(s).`;
        mensaje += `\nSe eliminará pero se mantendrá el registro histórico de las inscripciones.`;
    }

    if (confirm(mensaje)) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/membresias/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
            <input type="hidden" name="force_delete" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

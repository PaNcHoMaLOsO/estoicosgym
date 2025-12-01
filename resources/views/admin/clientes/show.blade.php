@extends('adminlte::page')

@section('title', 'Detalle Cliente - EstóicosGym')

@section('content_header')
@stop

@section('content')
@php
    // Obtener inscripción activa (excluyendo traspasadas, canceladas y cambiadas)
    $inscripcionActiva = $cliente->inscripciones->where('id_estado', 100)->first();
    $inscripcionPausada = $cliente->inscripciones->where('id_estado', 101)->first();
    $inscripcionVencida = $cliente->inscripciones->where('id_estado', 102)->first();
    // Excluir inscripciones que ya no son relevantes: 103=Cancelada, 105=Cambiada, 106=Traspasada
    $inscripcionesValidas = $cliente->inscripciones->whereNotIn('id_estado', [103, 105, 106]);
    $ultimaInscripcion = $inscripcionActiva ?? $inscripcionPausada ?? $inscripcionVencida ?? $inscripcionesValidas->first();
    
    // Traspasos (cedidos y recibidos)
    $traspasosCedidos = \App\Models\HistorialTraspaso::where('cliente_origen_id', $cliente->id)->with(['clienteDestino', 'membresia'])->get();
    $traspasosRecibidos = \App\Models\HistorialTraspaso::where('cliente_destino_id', $cliente->id)->with(['clienteOrigen', 'membresia'])->get();
    
    // Cambios de plan (upgrades/downgrades)
    $cambiosPlan = $cliente->inscripciones->where('es_cambio_plan', true);
    
    // Historial de pausas
    $historialPausas = \App\Models\HistorialCambio::where('entidad', 'inscripcion')
        ->whereIn('entidad_id', $cliente->inscripciones->pluck('id'))
        ->whereIn('tipo_cambio', ['pausa', 'reanudacion'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Calcular días restantes
    $diasRestantes = null;
    if ($inscripcionActiva && $inscripcionActiva->fecha_vencimiento) {
        $diasRestantes = now()->diffInDays($inscripcionActiva->fecha_vencimiento, false);
    }
    
    // Calcular edad
    $edad = $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->age : null;
    
    // Pendiente de pago
    $totalPendiente = $cliente->pagos->whereIn('id_estado', [200, 202])->sum('monto_pendiente');
@endphp

<div class="cliente-profile">
    <!-- Header Principal -->
    <div class="profile-header">
        <div class="header-bg"></div>
        
        <!-- Botón Volver -->
        <a href="{{ route('admin.clientes.index') }}" class="btn-volver">
            <i class="fas fa-arrow-left"></i>
            <span>Volver</span>
        </a>
        
        <!-- Acciones (arriba a la derecha) -->
        <div class="header-actions-top">
            <a href="{{ route('admin.clientes.edit', $cliente) }}" class="action-btn-top" title="Editar">
                <i class="fas fa-pen"></i>
            </a>
            <a href="{{ route('admin.inscripciones.create', ['cliente' => $cliente->id]) }}" class="action-btn-top primary" title="Nueva Inscripción">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        
        <!-- Contenido Principal del Header -->
        <div class="header-content">
            <!-- Avatar -->
            <div class="avatar-section">
                <div class="avatar {{ $cliente->activo ? '' : 'inactive' }}">
                    {{ strtoupper(substr($cliente->nombres, 0, 1) . substr($cliente->apellido_paterno, 0, 1)) }}
                </div>
                <span class="status-indicator {{ $cliente->activo ? 'active' : 'inactive' }}">
                    {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            
            <!-- Info del Cliente -->
            <div class="client-info">
                <h1 class="client-name">{{ $cliente->nombres }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}</h1>
                <div class="client-meta">
                    <span><i class="fas fa-id-card"></i> {{ $cliente->run_pasaporte ?? 'Sin RUT' }}</span>
                    @if($edad)
                    <span><i class="fas fa-birthday-cake"></i> {{ $edad }} años</span>
                    @endif
                    <span><i class="fas fa-calendar-alt"></i> Cliente desde {{ $cliente->created_at->format('M Y') }}</span>
                </div>
                
                <!-- Contacto Rápido -->
                <div class="quick-contact">
                    <a href="tel:{{ $cliente->celular }}" class="contact-pill">
                        <i class="fas fa-phone-alt"></i> {{ $cliente->celular }}
                    </a>
                    <a href="mailto:{{ $cliente->email }}" class="contact-pill">
                        <i class="fas fa-envelope"></i> {{ Str::limit($cliente->email, 30) }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Badges de Estado -->
        <div class="profile-badges">
            @if($cliente->convenio)
            <span class="badge badge-convenio">
                <i class="fas fa-building"></i>
                {{ $cliente->convenio->nombre }}
            </span>
            @endif
            @if($inscripcionActiva)
            <span class="badge badge-membresia">
                <i class="fas fa-crown"></i>
                {{ $inscripcionActiva->membresia->nombre ?? 'Membresía Activa' }}
            </span>
            @elseif($inscripcionPausada)
            <span class="badge badge-pausado">
                <i class="fas fa-pause-circle"></i>
                Membresía Pausada
            </span>
            @elseif($inscripcionVencida)
            <span class="badge badge-vencido">
                <i class="fas fa-exclamation-circle"></i>
                Membresía Vencida
            </span>
            @else
            <span class="badge badge-sin">
                <i class="fas fa-times-circle"></i>
                Sin Membresía
            </span>
            @endif
            @if($traspasosCedidos->count() > 0 || $traspasosRecibidos->count() > 0)
            <span class="badge badge-traspasos">
                <i class="fas fa-exchange-alt"></i>
                {{ $traspasosCedidos->count() + $traspasosRecibidos->count() }} Traspaso(s)
            </span>
            @endif
            @if($cambiosPlan->count() > 0)
            <span class="badge badge-upgrade">
                <i class="fas fa-arrow-up"></i>
                {{ $cambiosPlan->count() }} Cambio(s) Plan
            </span>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-teal">
                <i class="fas fa-dumbbell"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $cliente->inscripciones->count() }}</span>
                <span class="stat-label">Inscripciones</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-emerald">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">${{ number_format($cliente->pagos->sum('monto_abonado'), 0, ',', '.') }}</span>
                <span class="stat-label">Total Pagado</span>
            </div>
        </div>
        <div class="stat-card {{ $totalPendiente > 0 ? 'has-alert' : '' }}">
            <div class="stat-icon {{ $totalPendiente > 0 ? 'bg-amber' : 'bg-slate' }}">
                <i class="fas {{ $totalPendiente > 0 ? 'fa-exclamation-triangle' : 'fa-check' }}"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value {{ $totalPendiente > 0 ? 'text-amber' : '' }}">${{ number_format($totalPendiente, 0, ',', '.') }}</span>
                <span class="stat-label">Pendiente</span>
            </div>
        </div>
        <div class="stat-card {{ $diasRestantes !== null && $diasRestantes <= 7 ? 'has-alert' : '' }}">
            <div class="stat-icon {{ $diasRestantes === null ? 'bg-slate' : ($diasRestantes <= 3 ? 'bg-rose' : ($diasRestantes <= 7 ? 'bg-amber' : 'bg-sky')) }}">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $diasRestantes !== null ? ($diasRestantes > 0 ? $diasRestantes : '¡Hoy!') : '-' }}</span>
                <span class="stat-label">{{ $diasRestantes !== null ? 'Días Restantes' : 'Sin membresía' }}</span>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Left Column -->
        <div class="left-column">
            <!-- Inscripción Activa -->
            @if($ultimaInscripcion)
            <div class="card-active-membership">
                <div class="card-header-accent">
                    <div class="header-title">
                        <i class="fas fa-star"></i>
                        <span>{{ $inscripcionActiva ? 'Membresía Activa' : 'Última Membresía' }}</span>
                    </div>
                    @php
                        $estadoInsc = match($ultimaInscripcion->id_estado) {
                            100 => ['class' => 'activo', 'text' => 'Activa'],
                            101 => ['class' => 'pausado', 'text' => 'Pausada'],
                            102 => ['class' => 'vencido', 'text' => 'Vencida'],
                            103 => ['class' => 'cancelado', 'text' => 'Cancelada'],
                            104 => ['class' => 'suspendido', 'text' => 'Suspendida'],
                            105 => ['class' => 'cambiado', 'text' => 'Cambiada'],
                            106 => ['class' => 'traspasado', 'text' => 'Traspasada'],
                            default => ['class' => 'otro', 'text' => 'Otro']
                        };
                    @endphp
                    <span class="status-pill {{ $estadoInsc['class'] }}">{{ $estadoInsc['text'] }}</span>
                </div>
                <div class="card-body-membership">
                    <div class="membership-name">
                        <i class="fas fa-dumbbell"></i>
                        {{ $ultimaInscripcion->membresia->nombre ?? 'N/A' }}
                    </div>
                    
                    {{-- Alerta de pausa si está pausada --}}
                    @if($ultimaInscripcion->id_estado == 101)
                    <div class="pause-alert">
                        <i class="fas fa-pause-circle"></i>
                        <div class="pause-info">
                            <strong>Membresía Pausada</strong>
                            @if($ultimaInscripcion->fecha_pausa_inicio)
                            <span>Desde: {{ $ultimaInscripcion->fecha_pausa_inicio->format('d/m/Y') }}</span>
                            @endif
                            @if($ultimaInscripcion->dias_restantes_al_pausar)
                            <span>Días guardados: {{ $ultimaInscripcion->dias_restantes_al_pausar }}</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="membership-dates">
                        <div class="date-item">
                            <span class="date-label">Inicio</span>
                            <span class="date-value">{{ $ultimaInscripcion->fecha_inicio->format('d/m/Y') }}</span>
                        </div>
                        <div class="date-separator">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="date-item">
                            <span class="date-label">Vencimiento</span>
                            <span class="date-value {{ $ultimaInscripcion->id_estado == 102 ? 'expired' : '' }}">
                                {{ $ultimaInscripcion->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Días restantes (solo para activas) --}}
                    @if($ultimaInscripcion->id_estado == 100)
                    @php
                        $diasRestantes = now()->diffInDays($ultimaInscripcion->fecha_vencimiento, false);
                    @endphp
                    <div class="dias-restantes {{ $diasRestantes <= 7 ? 'warning' : ($diasRestantes <= 3 ? 'danger' : '') }}">
                        <i class="fas fa-hourglass-half"></i>
                        <span>{{ $diasRestantes > 0 ? $diasRestantes . ' días restantes' : 'Vence hoy' }}</span>
                    </div>
                    @endif

                    {{-- Descuentos aplicados --}}
                    @if($ultimaInscripcion->descuento_aplicado > 0)
                    <div class="descuentos-aplicados">
                        <div class="descuento-item">
                            <i class="fas fa-tag"></i>
                            <span>Descuento: ${{ number_format($ultimaInscripcion->descuento_aplicado, 0, ',', '.') }}</span>
                            @if($ultimaInscripcion->convenio)
                            <small>({{ $ultimaInscripcion->convenio->nombre }})</small>
                            @elseif($ultimaInscripcion->motivoDescuento)
                            <small>({{ $ultimaInscripcion->motivoDescuento->nombre }})</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="membership-price">
                        @if($ultimaInscripcion->descuento_aplicado > 0)
                        <div class="price-info">
                            <span class="price-label">Precio Base</span>
                            <span class="price-value strikethrough">${{ number_format($ultimaInscripcion->precio_base ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="price-info">
                            <span class="price-label">{{ $ultimaInscripcion->descuento_aplicado > 0 ? 'Precio Final' : 'Precio' }}</span>
                            <span class="price-value">${{ number_format($ultimaInscripcion->precio_final ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="price-info">
                            <span class="price-label">Pagado</span>
                            <span class="price-value paid">${{ number_format($ultimaInscripcion->pagos->sum('monto_abonado'), 0, ',', '.') }}</span>
                        </div>
                        @php
                            $pendiente = ($ultimaInscripcion->precio_final ?? 0) - $ultimaInscripcion->pagos->sum('monto_abonado');
                        @endphp
                        @if($pendiente > 0)
                        <div class="price-info">
                            <span class="price-label">Pendiente</span>
                            <span class="price-value pending">${{ number_format($pendiente, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('admin.inscripciones.show', $ultimaInscripcion) }}" class="btn-view-inscription">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                </div>
            </div>
            @else
            <div class="card-no-membership">
                <i class="fas fa-dumbbell"></i>
                <h4>Sin Membresía</h4>
                <p>Este cliente no tiene inscripciones registradas</p>
                <a href="{{ route('admin.inscripciones.create', ['cliente' => $cliente->id]) }}" class="btn-create-inscription">
                    <i class="fas fa-plus"></i> Crear Inscripción
                </a>
            </div>
            @endif

            <!-- Datos de Contacto -->
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-address-book"></i>
                    <h3>Datos de Contacto</h3>
                </div>
                <div class="info-body">
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="info-content">
                            <span class="info-label">Email</span>
                            <a href="mailto:{{ $cliente->email }}" class="info-value link">{{ $cliente->email }}</a>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-mobile-alt"></i></div>
                        <div class="info-content">
                            <span class="info-label">Celular</span>
                            <a href="tel:{{ $cliente->celular }}" class="info-value link">{{ $cliente->celular }}</a>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-birthday-cake"></i></div>
                        <div class="info-content">
                            <span class="info-label">Fecha Nacimiento</span>
                            <span class="info-value">{{ $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('d/m/Y') : 'No registrada' }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="info-content">
                            <span class="info-label">Dirección</span>
                            <span class="info-value">{{ $cliente->direccion ?? 'No registrada' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contacto de Emergencia -->
            @if($cliente->contacto_emergencia)
            <div class="info-card emergency">
                <div class="info-header">
                    <i class="fas fa-ambulance"></i>
                    <h3>Contacto de Emergencia</h3>
                </div>
                <div class="info-body">
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-user-shield"></i></div>
                        <div class="info-content">
                            <span class="info-label">Nombre</span>
                            <span class="info-value">{{ $cliente->contacto_emergencia }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="info-content">
                            <span class="info-label">Teléfono</span>
                            <a href="tel:{{ $cliente->telefono_emergencia }}" class="info-value link">{{ $cliente->telefono_emergencia }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if($cliente->observaciones)
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-sticky-note"></i>
                    <h3>Observaciones</h3>
                </div>
                <div class="info-body">
                    <p class="observaciones-text">{{ $cliente->observaciones }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="right-column">
            <!-- Historial de Inscripciones -->
            <div class="table-card">
                <div class="table-header">
                    <div class="header-title">
                        <i class="fas fa-history"></i>
                        <h3>Historial de Inscripciones</h3>
                    </div>
                    <span class="badge-count">{{ $cliente->inscripciones->count() }}</span>
                </div>
                <div class="table-body">
                    @if($cliente->inscripciones->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Membresía</th>
                                    <th>Período</th>
                                    <th>Estado</th>
                                    <th>Monto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->inscripciones as $inscripcion)
                                @php
                                    $estInsc = match($inscripcion->id_estado) {
                                        100 => ['class' => 'activo', 'text' => 'Activa'],
                                        101 => ['class' => 'pausado', 'text' => 'Pausada'],
                                        102 => ['class' => 'vencido', 'text' => 'Vencida'],
                                        103 => ['class' => 'cancelado', 'text' => 'Cancelada'],
                                        104 => ['class' => 'suspendido', 'text' => 'Suspendida'],
                                        105 => ['class' => 'cambiado', 'text' => 'Cambiada'],
                                        106 => ['class' => 'traspasado', 'text' => 'Traspasada'],
                                        default => ['class' => 'otro', 'text' => 'Otro']
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $inscripcion->membresia->nombre ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span class="date-range">
                                            {{ $inscripcion->fecha_inicio->format('d/m/y') }} - {{ $inscripcion->fecha_vencimiento->format('d/m/y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $estInsc['class'] }}">{{ $estInsc['text'] }}</span>
                                    </td>
                                    <td>
                                        <span class="monto">${{ number_format($inscripcion->precio_final ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inscripciones.show', $inscripcion) }}" class="btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="fas fa-dumbbell"></i>
                        <p>Sin inscripciones</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Pagos -->
            <div class="table-card">
                <div class="table-header">
                    <div class="header-title">
                        <i class="fas fa-credit-card"></i>
                        <h3>Historial de Pagos</h3>
                    </div>
                    <span class="badge-count">{{ $cliente->pagos->count() }}</span>
                </div>
                <div class="table-body">
                    @if($cliente->pagos->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->pagos->take(10) as $pago)
                                @php
                                    $estPago = match($pago->id_estado) {
                                        200 => ['class' => 'pendiente', 'text' => 'Pendiente'],
                                        201 => ['class' => 'pagado', 'text' => 'Pagado'],
                                        202 => ['class' => 'parcial', 'text' => 'Parcial'],
                                        203 => ['class' => 'vencido', 'text' => 'Vencido'],
                                        204 => ['class' => 'cancelado', 'text' => 'Cancelado'],
                                        default => ['class' => 'otro', 'text' => 'Otro']
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                    <td>
                                        @if($pago->inscripcion)
                                            {{ $pago->inscripcion->membresia->nombre ?? 'Membresía' }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td><span class="monto">${{ number_format($pago->monto_abonado, 0, ',', '.') }}</span></td>
                                    <td>{{ $pago->metodoPago?->nombre ?? '-' }}</td>
                                    <td><span class="status-badge {{ $estPago['class'] }}">{{ $estPago['text'] }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.pagos.show', $pago) }}" class="btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($cliente->pagos->count() > 10)
                    <div class="table-footer">
                        <span>Mostrando 10 de {{ $cliente->pagos->count() }} pagos</span>
                    </div>
                    @endif
                    @else
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>Sin pagos registrados</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Traspasos Cedidos -->
            @if($traspasosCedidos->count() > 0)
            <div class="table-card traspasos-card">
                <div class="table-header" style="background: linear-gradient(135deg, #6f42c1, #8b5cf6);">
                    <div class="header-title">
                        <i class="fas fa-exchange-alt"></i>
                        <h3>Traspasos Cedidos</h3>
                    </div>
                    <span class="badge-count" style="background: rgba(255,255,255,0.2);">{{ $traspasosCedidos->count() }}</span>
                </div>
                <div class="table-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Membresía</th>
                                    <th>Traspasada a</th>
                                    <th>Días</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($traspasosCedidos as $traspaso)
                                <tr>
                                    <td>
                                        <strong>{{ $traspaso->fecha_traspaso->format('d/m/Y') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge-membresia-small">{{ $traspaso->membresia->nombre ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($traspaso->clienteDestino)
                                            <a href="{{ route('admin.clientes.show', $traspaso->clienteDestino) }}" class="link-cliente">
                                                {{ $traspaso->clienteDestino->nombres }} {{ $traspaso->clienteDestino->apellido_paterno }}
                                            </a>
                                        @else
                                            <span class="text-muted">Cliente eliminado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="dias-badge">{{ $traspaso->dias_restantes_traspasados }} días</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.historial.traspaso.show', $traspaso) }}" class="btn-action" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Danger Zone -->
    @php
        // id_estado almacena el CÓDIGO directamente (100, 200, etc), no el ID del registro Estado
        $tieneInscripcionActiva = $cliente->inscripciones()->where('id_estado', 100)->exists();
        $tieneInscripcionPausada = $cliente->inscripciones()->where('id_estado', 101)->exists();
        $tienePagosPendientes = $cliente->pagos()->whereIn('id_estado', [200, 202])->exists(); // Pendiente o Parcial
        $puedoDesactivar = !$tieneInscripcionActiva && !$tieneInscripcionPausada && !$tienePagosPendientes;
    @endphp
    <div class="danger-zone">
        <div class="danger-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Zona de Peligro</h3>
        </div>
        <div class="danger-content">
            <div class="danger-info">
                <h4>Desactivar Cliente</h4>
                <p>El cliente no será eliminado, solo se ocultará de la lista activa.</p>
                @if(!$puedoDesactivar)
                <p class="warning-text">
                    <i class="fas fa-lock"></i> No disponible: 
                    @if($tieneInscripcionActiva)
                        tiene inscripciones activas.
                    @elseif($tieneInscripcionPausada)
                        tiene inscripciones pausadas.
                    @endif
                    @if($tienePagosPendientes)
                        @if($tieneInscripcionActiva || $tieneInscripcionPausada) Además, @endif
                        tiene pagos pendientes.
                    @endif
                </p>
                @endif
            </div>
            <button type="button" class="btn-danger-action" id="btnDesactivar" {{ !$puedoDesactivar ? 'disabled' : '' }}>
                <i class="fas fa-user-slash"></i> Desactivar
            </button>
        </div>
    </div>
</div>

<form id="formDesactivar" action="{{ route('admin.clientes.deactivate', $cliente) }}" method="POST" style="display:none;">
    @csrf
    @method('PATCH')
</form>
@stop

@section('css')
<style>
    /* ============================================
       PALETA DE COLORES - ESTOICOS GYM
       Basada en el sidebar del sistema
       ============================================ */
    :root {
        /* Colores principales del sistema */
        --primary: #1a1a2e;
        --primary-light: #16213e;
        --accent: #e94560;
        --accent-light: #ff6b6b;
        
        /* Colores de estado */
        --success: #00bf8e;
        --warning: #f0a500;
        --danger: #dc3545;
        --info: #4361ee;
        
        /* Grises */
        --gray-50: #f8f9fa;
        --gray-100: #f1f3f5;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-400: #ced4da;
        --gray-500: #adb5bd;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
        --gray-900: #212529;
        
        /* Texto */
        --text-primary: #212529;
        --text-secondary: #6c757d;
        --text-muted: #adb5bd;
        
        /* Fondos */
        --bg-page: #f4f6f9;
        --bg-card: #ffffff;
        
        /* Bordes y sombras */
        --border-color: #e9ecef;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
        --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
        
        /* Radios */
        --radius-sm: 6px;
        --radius-md: 10px;
        --radius-lg: 16px;
        --radius-xl: 24px;
    }

    .content-wrapper { 
        background: #f0f2f5 !important; 
        min-height: 100vh;
    }

    .cliente-profile {
        padding: 24px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* ============================================
       PROFILE HEADER - REDISEÑADO
       ============================================ */
    .profile-header {
        background: white;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(26, 26, 46, 0.1);
        overflow: hidden;
        position: relative;
    }

    .header-bg {
        height: 110px;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        position: relative;
    }

    .header-bg::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: linear-gradient(to top, rgba(255,255,255,0.1), transparent);
    }

    .btn-volver {
        position: absolute;
        top: 16px;
        left: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
        color: white;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        padding: 8px 14px;
        background: rgba(255,255,255,0.12);
        border-radius: 8px;
        transition: all 0.2s;
        z-index: 10;
        backdrop-filter: blur(4px);
    }

    .btn-volver:hover {
        background: rgba(255,255,255,0.2);
        color: white;
        transform: translateX(-3px);
    }

    /* Acciones en la esquina superior derecha */
    .header-actions-top {
        position: absolute;
        top: 16px;
        right: 16px;
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .action-btn-top {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.12);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s;
        backdrop-filter: blur(4px);
        font-size: 16px;
    }

    .action-btn-top:hover {
        background: rgba(255,255,255,0.25);
        color: white;
        transform: scale(1.08);
    }

    .action-btn-top.primary {
        background: #e94560;
        box-shadow: 0 4px 12px rgba(233, 69, 96, 0.4);
    }

    .action-btn-top.primary:hover {
        background: #ff6b85;
        box-shadow: 0 6px 16px rgba(233, 69, 96, 0.5);
    }

    /* Contenido del Header */
    .header-content {
        display: flex;
        align-items: flex-start;
        gap: 28px;
        padding: 0 28px 28px;
        margin-top: -55px;
        position: relative;
    }

    /* Avatar Section */
    .avatar-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .avatar {
        width: 110px;
        height: 110px;
        background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 40px;
        font-weight: 700;
        border: 5px solid white;
        box-shadow: 0 8px 24px rgba(26, 26, 46, 0.25);
        letter-spacing: -1px;
    }

    .avatar.inactive {
        background: linear-gradient(145deg, #6c757d 0%, #5a6268 100%);
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-indicator.active {
        background: #d4edda;
        color: #155724;
    }

    .status-indicator.inactive {
        background: #f8d7da;
        color: #721c24;
    }

    /* Client Info */
    .client-info {
        flex: 1;
        padding-top: 60px;
    }

    .client-name {
        font-size: 30px;
        font-weight: 800;
        color: #1a1a2e;
        margin: 0 0 10px 0;
        line-height: 1.15;
        letter-spacing: -0.5px;
    }

    .client-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 18px;
    }

    .client-meta span {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 14px;
        color: #6c757d;
    }

    .client-meta i {
        color: #1a1a2e;
        font-size: 14px;
        opacity: 0.7;
    }

    /* Quick Contact Pills */
    .quick-contact {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .contact-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        background: #f8f9fa;
        color: #495057;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.25s ease;
        border: 1px solid #e9ecef;
    }

    .contact-pill:hover {
        background: #1a1a2e;
        color: white;
        border-color: #1a1a2e;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 26, 46, 0.2);
    }

    .contact-pill i {
        font-size: 15px;
    }    .contact-pill i {
        font-size: 14px;
    }

    /* Profile Badges */
    .profile-badges {
        padding: 16px 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        border-top: 1px solid #e9ecef;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge-convenio { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1d4ed8; }
    .badge-membresia { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #15803d; }
    .badge-pausado { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #b45309; }
    .badge-vencido { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #b91c1c; }
    .badge-sin { background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); color: #6c757d; }
    .badge-traspasos { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #7c3aed; }
    .badge-upgrade { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; }

    /* ============================================
       STATS GRID
       ============================================ */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: all 0.25s ease;
        border: 1px solid #e9ecef;
    }

    .stat-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }

    .stat-card.has-alert {
        border-color: #f0a500;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Iconos con colores del sistema */
    .bg-teal { 
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    }
    .bg-emerald { 
        background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%);
    }
    .bg-amber { 
        background: linear-gradient(135deg, #f0a500 0%, #d99400 100%);
    }
    .bg-rose { 
        background: linear-gradient(135deg, #e94560 0%, #d63a55 100%);
    }
    .bg-sky { 
        background: linear-gradient(135deg, #4361ee 0%, #3651d4 100%);
    }
    .bg-slate { 
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    }

    .stat-content {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-size: 26px;
        font-weight: 800;
        color: #1a1a2e;
        line-height: 1.15;
        letter-spacing: -0.3px;
    }

    .stat-value.text-amber { color: #d68f00; }

    .stat-label {
        font-size: 13px;
        color: #6c757d;
        margin-top: 5px;
        font-weight: 500;
    }

    /* ============================================
       CONTENT GRID
       ============================================ */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 24px;
    }

    .left-column, .right-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Cards Base */
    .info-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .info-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        padding: 18px 22px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .info-card .card-header i {
        color: #1a1a2e;
        font-size: 20px;
    }

    .info-card .card-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .info-card .card-body {
        padding: 22px;
    }

    /* ============================================
       ACTIVE MEMBERSHIP CARD (Tarjeta Membresía Activa)
       ============================================ */
    .card-active-membership {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 2px solid rgba(26,26,46,0.15);
    }

    .card-header-accent {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        padding: 18px 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header-accent .header-title {
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
        font-size: 17px;
        font-weight: 700;
    }

    .card-header-accent .header-title i {
        font-size: 20px;
    }

    .status-pill {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(255,255,255,0.2);
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-pill.activo { background: rgba(0, 191, 142, 0.35); }
    .status-pill.pausado { background: rgba(240, 165, 0, 0.35); }
    .status-pill.vencido { background: rgba(233, 69, 96, 0.35); }
    .status-pill.cancelado { background: rgba(100, 116, 139, 0.35); }
    .status-pill.suspendido { background: rgba(190, 24, 93, 0.35); }
    .status-pill.cambiado { background: rgba(67, 97, 238, 0.35); }
    .status-pill.traspasado { background: rgba(139, 92, 246, 0.35); }

    .card-body-membership {
        padding: 22px;
    }

    .membership-name {
        font-size: 24px;
        font-weight: 800;
        color: #1a1a2e;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .membership-name i {
        color: #e94560;
    }

    /* Días restantes */
    .dias-restantes {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-weight: 600;
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #15803d;
    }

    .dias-restantes.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #b45309;
    }

    .dias-restantes.danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #b91c1c;
    }

    /* Descuentos aplicados */
    .descuentos-aplicados {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 18px;
    }

    .descuento-item {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #1d4ed8;
        font-weight: 600;
    }

    .descuento-item small {
        opacity: 0.7;
    }

    /* Membership price grid */
    .membership-price {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .price-info {
        text-align: center;
        padding: 14px;
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }

    .price-label {
        display: block;
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .price-value {
        font-size: 20px;
        font-weight: 800;
        color: #1a1a2e;
    }

    .price-value.strikethrough {
        text-decoration: line-through;
        opacity: 0.5;
        font-size: 14px;
    }

    .price-value.paid { color: #00bf8e; }
    .price-value.pending { color: #f0a500; }

    .btn-view-inscription {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px;
        background: rgba(26,26,46,0.05);
        color: #1a1a2e;
        border: 2px solid rgba(26,26,46,0.15);
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .btn-view-inscription:hover {
        background: #1a1a2e;
        border-color: #1a1a2e;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26,26,46,0.2);
    }

    /* No membership card */
    .card-no-membership {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 50px 40px;
        text-align: center;
        border: 3px dashed #dee2e6;
    }

    .card-no-membership i {
        font-size: 52px;
        color: #dee2e6;
        margin-bottom: 18px;
    }

    .card-no-membership h4 {
        font-size: 20px;
        color: #1a1a2e;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .card-no-membership p {
        color: #6c757d;
        margin-bottom: 24px;
    }

    .btn-create-inscription {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.25s ease;
    }

    .btn-create-inscription:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26,26,46,0.3);
        color: white;
    }

    /* Info Card Headers and Body */
    .info-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        padding: 18px 22px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .info-header i {
        color: #1a1a2e;
        font-size: 20px;
    }

    .info-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .info-body {
        padding: 22px;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px;
        background: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 12px;
        transition: all 0.25s ease;
        border: 1px solid transparent;
    }

    .info-row:last-child {
        margin-bottom: 0;
    }

    .info-row:hover {
        background: #f1f3f5;
        border-color: #e9ecef;
        transform: translateX(4px);
    }

    .info-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, rgba(26,26,46,0.1) 0%, rgba(26,26,46,0.15) 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-icon i {
        color: #1a1a2e;
        font-size: 18px;
    }

    .info-content {
        flex: 1;
    }

    .info-label {
        display: block;
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 3px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .info-value {
        font-size: 15px;
        color: #1a1a2e;
        font-weight: 600;
    }

    .info-value.link {
        color: #4361ee;
        text-decoration: none;
    }

    .info-value.link:hover {
        text-decoration: underline;
        color: #3651d4;
    }

    /* Emergency card */
    .info-card.emergency .info-header {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .info-card.emergency .info-header i {
        color: #e94560;
    }

    .info-card.emergency .info-icon {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    }

    .info-card.emergency .info-icon i {
        color: #e94560;
    }

    /* Observaciones */
    .observaciones-text {
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        padding: 18px;
        border-radius: 12px;
        color: #1a1a2e;
        line-height: 1.7;
        font-size: 14px;
        border-left: 5px solid #1a1a2e;
        margin: 0;
    }

    /* Membership Dates (grid de fechas) */
    .membership-dates {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 18px;
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        padding: 18px;
        border-radius: 12px;
        margin-bottom: 18px;
        border: 1px solid #e9ecef;
    }

    .date-item {
        text-align: center;
    }

    .date-label {
        display: block;
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .date-value {
        font-size: 17px;
        font-weight: 700;
        color: #1a1a2e;
    }

    .date-value.expired {
        color: #e94560;
    }

    .date-separator {
        color: #dee2e6;
        font-size: 22px;
    }

    /* Días restantes alert */
    .days-remaining {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-weight: 600;
    }

    .days-remaining.success {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #15803d;
    }

    .days-remaining.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #b45309;
    }

    .days-remaining.danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #b91c1c;
    }

    /* Pause alert */
    .pause-alert {
        background: linear-gradient(135deg, #fef3c7 0%, #fef9c3 100%);
        border: 1px solid #fcd34d;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .pause-alert i {
        font-size: 26px;
        color: #f0a500;
    }

    .pause-alert .pause-info strong {
        display: block;
        color: #92400e;
        margin-bottom: 5px;
        font-size: 15px;
    }

    .pause-alert .pause-info span {
        font-size: 13px;
        color: #a16207;
    }

    /* Descuentos */
    .discount-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 18px;
    }

    .discount-badge small {
        opacity: 0.8;
    }

    /* Price Grid */
    .price-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .price-item {
        text-align: center;
        padding: 14px;
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }

    .price-item .label {
        display: block;
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .price-item .value {
        font-size: 20px;
        font-weight: 800;
        color: #1a1a2e;
    }

    .price-item .value.strikethrough {
        text-decoration: line-through;
        opacity: 0.5;
        font-size: 14px;
    }

    .price-item .value.success { color: #00bf8e; }
    .price-item .value.warning { color: #f0a500; }
    .price-item .value.danger { color: #e94560; }

    /* Contact Info Card */
    .contact-grid {
        display: grid;
        gap: 16px;
    }

    .contact-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px;
        background: #f8f9fa;
        border-radius: 12px;
        transition: all 0.25s ease;
        border: 1px solid transparent;
    }

    .contact-row:hover {
        background: #f1f3f5;
        border-color: #e9ecef;
        transform: translateX(4px);
    }

    .contact-row .icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, rgba(26,26,46,0.1) 0%, rgba(26,26,46,0.15) 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a1a2e;
    }

    .contact-row .content {
        flex: 1;
    }

    .contact-row .label {
        display: block;
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 3px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .contact-row .value {
        font-size: 15px;
        color: #1a1a2e;
        font-weight: 600;
    }

    .contact-row a.value {
        color: #4361ee;
        text-decoration: none;
    }

    .contact-row a.value:hover {
        text-decoration: underline;
        color: #3651d4;
    }

    /* Emergency Card */
    .emergency-card {
        border-color: rgba(233,69,96,0.2);
    }

    .emergency-card .card-header {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .emergency-card .card-header i {
        color: #e94560;
    }

    /* Tables */
    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .table-card .table-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        padding: 18px 22px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-card .header-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .table-card .header-title i {
        color: #1a1a2e;
        font-size: 20px;
    }

    .table-card .header-title h3 {
        font-size: 17px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .badge-count {
        background: linear-gradient(135deg, rgba(26,26,46,0.1) 0%, rgba(26,26,46,0.15) 100%);
        color: #1a1a2e;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }

    .table-body {
        padding: 0;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8f9fa;
        padding: 14px 18px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e9ecef;
    }

    .data-table td {
        padding: 16px 18px;
        font-size: 14px;
        color: #1a1a2e;
        border-bottom: 1px solid #e9ecef;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-badge.activo, .status-badge.pagado { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #15803d; }
    .status-badge.vencido { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #b91c1c; }
    .status-badge.pausado, .status-badge.parcial { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #b45309; }
    .status-badge.pendiente { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1d4ed8; }
    .status-badge.cancelado { background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); color: #6c757d; }
    .status-badge.suspendido { background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%); color: #be185d; }
    .status-badge.cambiado { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; }
    .status-badge.traspasado { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #7c3aed; }

    .btn-action {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f3f5;
        color: #6c757d;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .btn-action:hover {
        background: #1a1a2e;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(26,26,46,0.2);
    }

    .empty-state {
        padding: 45px;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 36px;
        color: #dee2e6;
        margin-bottom: 14px;
    }

    .table-footer {
        padding: 14px 18px;
        text-align: center;
        font-size: 13px;
        color: #6c757d;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    /* Traspasos Card */
    .traspasos-card .table-header {
        background: linear-gradient(135deg, #6f42c1 0%, #7c3aed 100%);
        color: white;
    }

    .traspasos-card .header-title i,
    .traspasos-card .header-title h3 {
        color: white;
    }

    .traspasos-card .badge-count {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    .link-cliente {
        color: #4361ee;
        text-decoration: none;
        font-weight: 600;
    }

    .link-cliente:hover {
        text-decoration: underline;
        color: #3651d4;
    }

    .dias-badge {
        background: linear-gradient(135deg, #f1f3f5 0%, #e9ecef 100%);
        color: #495057;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    /* Danger Zone */
    .danger-zone {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 2px solid rgba(233,69,96,0.25);
        margin-top: 28px;
    }

    .danger-header {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-bottom: 1px solid rgba(233,69,96,0.2);
    }

    .danger-header i {
        color: #e94560;
        font-size: 22px;
    }

    .danger-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #e94560;
        margin: 0;
    }

    .danger-content {
        padding: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
    }

    .danger-info h4 {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0 0 8px 0;
    }

    .danger-info p {
        font-size: 14px;
        color: #6c757d;
        margin: 0;
    }

    .danger-info .warning-text {
        color: #e94560;
        font-weight: 600;
        margin-top: 10px;
    }

    .btn-danger-action {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #e94560 0%, #d63a55 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    .btn-danger-action:hover:not(:disabled) {
        background: linear-gradient(135deg, #d63a55 0%, #c42f4a 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(233,69,96,0.35);
    }

    .btn-danger-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .content-grid { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .header-content { flex-direction: column; align-items: center; text-align: center; }
        .client-info { padding-top: 20px; text-align: center; }
        .client-meta { justify-content: center; }
        .quick-contact { justify-content: center; }
    }

    @media (max-width: 768px) {
        .cliente-profile { padding: 12px; }
        .header-bg { height: 85px; }
        .header-content { margin-top: -45px; padding: 0 16px 18px; }
        .avatar { width: 85px; height: 85px; font-size: 30px; }
        .client-name { font-size: 24px; }
        .client-meta { flex-direction: column; gap: 10px; }
        .quick-contact { flex-direction: column; }
        .contact-pill { width: 100%; justify-content: center; }
        .profile-badges { padding: 14px 18px; justify-content: center; }
        .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
        .stat-card { padding: 16px; }
        .stat-icon { width: 44px; height: 44px; font-size: 18px; }
        .stat-value { font-size: 20px; }
        .danger-content { flex-direction: column; text-align: center; }
        .btn-danger-action { width: 100%; justify-content: center; }
        .membership-dates { grid-template-columns: 1fr; gap: 14px; }
        .date-separator { display: none; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* SweetAlert2 Custom Theme - EstoicosGym */
    .swal2-popup.swal-estoicos {
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .swal2-popup.swal-estoicos .swal2-title {
        color: #1a1a2e;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .swal2-popup.swal-estoicos .swal2-html-container {
        color: #64748b;
        font-size: 1rem;
    }
    .swal-estoicos .swal2-confirm {
        background: linear-gradient(135deg, #e94560 0%, #c73e55 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 15px rgba(233, 69, 96, 0.4) !important;
        transition: all 0.3s ease !important;
    }
    .swal-estoicos .swal2-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(233, 69, 96, 0.5) !important;
    }
    .swal-estoicos .swal2-cancel {
        background: #f1f5f9 !important;
        color: #64748b !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 28px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    .swal-estoicos .swal2-cancel:hover {
        background: #e2e8f0 !important;
    }
    .swal-estoicos.swal-success .swal2-confirm {
        background: linear-gradient(135deg, #00bf8e 0%, #00a67d 100%) !important;
        box-shadow: 0 4px 15px rgba(0, 191, 142, 0.4) !important;
    }
    .swal-estoicos.swal-warning .swal2-confirm {
        background: linear-gradient(135deg, #f0a500 0%, #d99400 100%) !important;
        box-shadow: 0 4px 15px rgba(240, 165, 0, 0.4) !important;
    }
    .swal-estoicos.swal-primary .swal2-confirm {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
        box-shadow: 0 4px 15px rgba(26, 26, 46, 0.4) !important;
    }
    .info-card-swal {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        padding: 16px;
        margin-top: 1rem;
        border: 1px solid #f0a500;
        text-align: left;
    }
    .info-card-swal h6 {
        color: #b45309;
        font-weight: 700;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-card-swal ul {
        color: #78350f;
        margin: 0;
        padding-left: 20px;
        font-size: 14px;
    }
    .info-card-swal li {
        margin-bottom: 6px;
    }
</style>
<script>
$(document).ready(function() {
    $('#btnDesactivar').on('click', function() {
        Swal.fire({
            title: '¿Desactivar cliente?',
            html: `
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-user-slash" style="font-size: 2rem; color: #b45309;"></i>
                    </div>
                    <p style="font-weight: 600; color: #1e293b; font-size: 1.1rem; margin-bottom: 0.5rem;">{{ $cliente->nombres }} {{ $cliente->apellido_paterno }}</p>
                </div>
                <div class="info-card-swal">
                    <h6><i class="fas fa-info-circle"></i> ¿Qué sucederá?</h6>
                    <ul>
                        <li>El cliente <strong>NO</strong> será eliminado</li>
                        <li>Su historial se conservará intacto</li>
                        <li>No aparecerá en la lista de activos</li>
                        <li>Podrá ser reactivado en cualquier momento</li>
                    </ul>
                </div>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-user-slash"></i> Sí, desactivar',
            cancelButtonText: '<i class="fas fa-arrow-left"></i> Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'swal-estoicos swal-warning',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    html: '<div style="padding: 2rem;"><div style="width: 50px; height: 50px; border: 4px solid #fef3c7; border-top-color: #f0a500; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div></div><style>@keyframes spin { to { transform: rotate(360deg); } }</style>',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    customClass: { popup: 'swal-estoicos' }
                });
                $('#formDesactivar').submit();
            }
        });
    });

    @if(session('success'))
    Swal.fire({
        title: '¡Operación exitosa!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-check" style="font-size: 2rem; color: #00bf8e;"></i>
                </div>
                <p style="color: #64748b;">{{ session('success') }}</p>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Continuar',
        timer: 4000,
        timerProgressBar: true,
        customClass: {
            popup: 'swal-estoicos swal-success',
            confirmButton: 'swal2-confirm'
        },
        buttonsStyling: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        title: '¡Ocurrió un error!',
        html: `
            <div style="text-align: center; padding: 1rem 0;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #dc3545;"></i>
                </div>
                <p style="color: #64748b;">{{ session('error') }}</p>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Entendido',
        customClass: {
            popup: 'swal-estoicos',
            confirmButton: 'swal2-confirm'
        },
        buttonsStyling: false
    });
    @endif
});
</script>
@stop
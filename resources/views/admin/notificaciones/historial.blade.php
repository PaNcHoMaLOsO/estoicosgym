@extends('adminlte::page')

@section('title', 'Historial de Ejecuciones Automáticas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-history mr-2"></i>
            Historial de Ejecuciones Automáticas
        </h1>
        <a href="{{ route('admin.notificaciones.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Info sobre CRON -->
    <div class="alert" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: none; border-left: 4px solid #2196F3; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
        <div class="d-flex align-items-start">
            <div class="mr-3" style="font-size: 2rem; color: #1976D2;">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h5 class="mb-2" style="color: #1565C0; font-weight: 600;">
                    Sistema de Notificaciones Automáticas
                </h5>
                <p class="mb-2 text-muted">
                    Las notificaciones se ejecutan automáticamente mediante tareas programadas (CRON):
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0 text-muted" style="line-height: 1.8;">
                            <li><strong>01:00 AM:</strong> Actualización de estados de membresías</li>
                            <li><strong>02:00 AM:</strong> Sincronización de pagos</li>
                            <li><strong>03:00 AM:</strong> Desactivación de membresías vencidas</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0 text-muted" style="line-height: 1.8;">
                            <li><strong>08:00 AM:</strong> 📧 Envío de notificaciones programadas</li>
                            <li><strong>02:00 PM:</strong> 🔄 Reintento de notificaciones fallidas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Historial -->
    <div class="card shadow-sm" style="border-radius: 15px; border: none;">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0; padding: 20px;">
            <h5 class="mb-0 text-white font-weight-bold">
                <i class="fas fa-calendar-alt mr-2"></i>
                Historial de Ejecuciones (Últimos 30 días)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 15px; font-weight: 600; color: #495057;">Fecha</th>
                            <th style="padding: 15px; font-weight: 600; color: #495057; text-align: center;">Total</th>
                            <th style="padding: 15px; font-weight: 600; color: #495057; text-align: center;">Pendientes</th>
                            <th style="padding: 15px; font-weight: 600; color: #495057; text-align: center;">Enviadas</th>
                            <th style="padding: 15px; font-weight: 600; color: #495057; text-align: center;">Fallidas</th>
                            <th style="padding: 15px; font-weight: 600; color: #495057; text-align: center;">Canceladas</th>
                            <th style="padding: 15px; font-weight: 600; color: #495057; text-align: center;">Tasa Éxito</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historial as $registro)
                            @php
                                $tasaExito = $registro->total > 0 ? round(($registro->enviadas / $registro->total) * 100, 1) : 0;
                                $colorTasa = $tasaExito >= 90 ? 'success' : ($tasaExito >= 70 ? 'warning' : 'danger');
                            @endphp
                            <tr>
                                <td style="padding: 15px; vertical-align: middle;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar mr-2 text-primary"></i>
                                        <strong>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</strong>
                                        <small class="text-muted ml-2">{{ \Carbon\Carbon::parse($registro->fecha)->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    <span class="badge badge-secondary" style="font-size: 0.9rem; padding: 6px 12px;">
                                        {{ $registro->total }}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    <span class="badge badge-warning" style="font-size: 0.9rem; padding: 6px 12px;">
                                        {{ $registro->pendientes }}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    <span class="badge badge-success" style="font-size: 0.9rem; padding: 6px 12px;">
                                        {{ $registro->enviadas }}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    @if($registro->fallidas > 0)
                                        <span class="badge badge-danger" style="font-size: 0.9rem; padding: 6px 12px;">
                                            {{ $registro->fallidas }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    @if($registro->canceladas > 0)
                                        <span class="badge badge-secondary" style="font-size: 0.9rem; padding: 6px 12px;">
                                            {{ $registro->canceladas }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="padding: 15px; text-align: center; vertical-align: middle;">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress" style="width: 100px; height: 20px; margin-right: 10px;">
                                            <div class="progress-bar bg-{{ $colorTasa }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $tasaExito }}%;" 
                                                 aria-valuenow="{{ $tasaExito }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <strong class="text-{{ $colorTasa }}">{{ $tasaExito }}%</strong>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 40px; text-align: center;">
                                    <div style="color: #999;">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">No hay registros de ejecuciones automáticas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($historial->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $historial->links() }}
        </div>
    @endif

    <!-- Estadísticas Generales -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-body text-center">
                    <div style="font-size: 2rem; color: #6c757d; margin-bottom: 10px;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="mb-0 font-weight-bold">{{ $historial->sum('total') }}</h3>
                    <p class="text-muted mb-0">Total Notificaciones</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-body text-center">
                    <div style="font-size: 2rem; color: #28a745; margin-bottom: 10px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-success">{{ $historial->sum('enviadas') }}</h3>
                    <p class="text-muted mb-0">Enviadas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-body text-center">
                    <div style="font-size: 2rem; color: #dc3545; margin-bottom: 10px;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-danger">{{ $historial->sum('fallidas') }}</h3>
                    <p class="text-muted mb-0">Fallidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-body text-center">
                    <div style="font-size: 2rem; color: #ffc107; margin-bottom: 10px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-0 font-weight-bold text-warning">{{ $historial->sum('pendientes') }}</h3>
                    <p class="text-muted mb-0">Pendientes</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
</style>
@stop

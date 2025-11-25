@extends('adminlte::page')

@section('title', 'Nueva Inscripción - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Crear Nueva Inscripción</h1>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.inscripciones.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_cliente">Cliente <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_cliente') is-invalid @enderror" 
                                    id="id_cliente" name="id_cliente" required>
                                <option value="">-- Seleccionar Cliente --</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ old('id_cliente') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombres }} {{ $cliente->apellido_paterno }} ({{ $cliente->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_cliente')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_estado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control @error('id_estado') is-invalid @enderror" 
                                    id="id_estado" name="id_estado" required>
                                <option value="">-- Seleccionar Estado --</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}" {{ old('id_estado') == $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_estado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', now()->format('Y-m-d')) }}" required>
                            @error('fecha_inicio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha_vencimiento">Fecha Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" 
                                   id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required>
                            @error('fecha_vencimiento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Nota:</strong> La inscripción se crea con la fecha de hoy. Ajusta las fechas según sea necesario.
                </div>

                <hr>

                <div class="form-group">
                    <a href="{{ route('admin.inscripciones.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Inscripción
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

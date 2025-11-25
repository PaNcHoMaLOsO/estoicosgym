@extends('adminlte::page')

@section('title', 'Crear Cliente - EstóicosGym')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Crear Nuevo Cliente</h1>
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
            <form action="{{ route('admin.clientes.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombres">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombres') is-invalid @enderror" 
                                   id="nombres" name="nombres" value="{{ old('nombres') }}" required>
                            @error('nombres')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('apellidos') is-invalid @enderror" 
                                   id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required>
                            @error('apellidos')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" name="telefono" value="{{ old('telefono') }}" required>
                            @error('telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ciudad">Ciudad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ciudad') is-invalid @enderror" 
                                   id="ciudad" name="ciudad" value="{{ old('ciudad') }}" required>
                            @error('ciudad')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activo">Estado</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" 
                                       {{ old('activo') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

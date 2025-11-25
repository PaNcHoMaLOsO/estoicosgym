@extends('adminlte::page')

@section('title', 'Dashboard Test')

@section('content_header')
    <h1>Dashboard de Prueba</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">AdminLTE 3 está funcionando</h3>
                </div>
                <div class="card-body">
                    <p>Si ves esto con estilos de AdminLTE, entonces está todo OK.</p>
                    <p class="text-muted">Las columnas requeridas existen, pero falta verificar los datos en la BD.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

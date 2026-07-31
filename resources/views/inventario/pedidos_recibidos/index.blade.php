@extends('layouts.app')

@section('title', 'Pedidos Recibidos - Pendientes')

@section('content')
    @include('inventario.pedidos_recibidos.partials.listado', ['tipo' => 'pendiente'])
@endsection

@push('scripts')
    @vite(['resources/css/inventario/pedidos_recibidos/pedidos.css', 'resources/js/inventario/pedidos_recibidos/pedidos.js'])
@endpush

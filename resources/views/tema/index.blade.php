@extends('layouts.app')

@section('title', 'Cambiar Tema - Hospital General')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tema/tema.css') }}">
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Encabezado ──────────────────────────────────── --}}
    <div class="tema-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1><i class="bi bi-palette-fill me-2"></i>Skin del Sistema</h1>
            <p>Selecciona el tema de color que deseas aplicar en todo el sistema</p>
        </div>
        <span class="tema-count-badge">
            <i class="bi bi-palette me-1"></i>{{ count($themes) }} temas disponibles
        </span>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 text-success"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Grid de tarjetas ─────────────────────────── --}}
    <div class="tema-grid">
        @foreach($themes as $tema)
            @php
                $isCurrent = (Auth::user()->tema === $tema['id']);
                
                // Calculate luminance for each theme choice to decide hover button text color
                $hex = str_replace('#', '', $tema['color']);
                if (strlen($hex) == 3) {
                    $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                    $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                    $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
                } else {
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                }
                $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                $btnHoverText = $yiq >= 150 ? '#000000' : '#ffffff';
            @endphp

            <div class="tema-card {{ $isCurrent ? 'active-theme' : '' }}"
                 style="{{ $isCurrent ? 'border-color:' . $tema['color'] . ';' : '' }}">

                {{-- Zona de color --}}
                <div class="tema-card-color" style="background-color: {{ $tema['color'] }};">
                    <div class="color-circle">
                        <i class="bi bi-palette-fill"></i>
                    </div>

                    @if($isCurrent)
                        <span class="tema-badge-active">
                            <i class="bi bi-check-circle-fill"></i> Activo
                        </span>
                    @endif
                </div>

                {{-- Cuerpo --}}
                <div class="tema-card-body">
                    <div class="tema-card-name">{{ $tema['nombre'] }}</div>

                    @if($isCurrent)
                        <span class="tema-btn-selected">
                            <i class="bi bi-check2-circle"></i> Seleccionado
                        </span>
                    @else
                        <form action="{{ route('cambiar_tema.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="color" value="{{ $tema['id'] }}">
                            <button type="submit" class="tema-btn"
                                style="color: {{ $tema['color'] }}; border-color: {{ $tema['color'] }};"
                                onmouseover="this.style.backgroundColor='{{ $tema['color'] }}'; this.style.color='{{ $btnHoverText }}';"
                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $tema['color'] }}';">
                                <i class="bi bi-cursor-fill"></i> Seleccionar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Footer ──────────────────────────────────────── --}}
    <div class="tema-footer">
        <i class="bi bi-info-circle text-primary"></i>
        <span>El tema seleccionado se aplica de inmediato y se guarda de forma permanente en tu perfil de usuario.</span>
    </div>

</div>
@endsection

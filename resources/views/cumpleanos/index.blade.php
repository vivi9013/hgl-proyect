@extends('layouts.app')

@section('title', 'Cumpleaños - Hospital General')

@push('styles')
@vite(['resources/css/cumpleanos/cumpleanos.css'])
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Encabezado ──────────────────────────────────── --}}
    <div class="cumple-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1><i class="bi bi-cake2-fill me-2"></i>Cumpleaños</h1>
            <p>Lista de cumpleaños del mes de <strong>{{ ucfirst($nombreMes) }}</strong></p>
        </div>
        <span class="cumple-badge-count">
            <i class="bi bi-people-fill me-1"></i>
            {{ $cumpleaneros->count() }} {{ $cumpleaneros->count() === 1 ? 'persona' : 'personas' }}
        </span>
    </div>

    {{-- ── Grid de cumpleañeros ────────────────────────── --}}
    @if($cumpleaneros->isEmpty())
        <div class="cumple-empty">
            <i class="bi bi-calendar-x"></i>
            <h4>Sin cumpleaños este mes</h4>
            <p class="text-muted">No hay colaboradores activos con cumpleaños en {{ ucfirst($nombreMes) }}.</p>
        </div>
    @else
        <div class="cumple-grid">
            @foreach($cumpleaneros as $index => $persona)
                @php
                    $dia    = \Carbon\Carbon::parse($persona->fecha_nac)->day;
                    $edad   = \Carbon\Carbon::parse($persona->fecha_nac)->age;
                    $nombre = $persona->nombre . ' ' . $persona->ap_paterno;
                    $dept   = optional(optional($persona->trabajador)->departamento)->nombre ?? '—';
                    $sede   = optional($persona->sede)->abreviatura ?? '—';
                    $color  = $colores[$index % count($colores)];

                    // Porcentaje del día en el mes actual
                    $diasMes = \Carbon\Carbon::now()->daysInMonth;
                    $pct     = round(($dia / $diasMes) * 100);
                @endphp

                <div class="cumple-card" style="background-color: {{ $color }};">
                    {{-- Día --}}
                    <div class="cumple-card-day">{{ $dia }}</div>

                    {{-- Info --}}
                    <div class="cumple-card-body">
                        <div class="cumple-card-name">{{ $nombre }}</div>
                        <div class="cumple-card-age">{{ $edad }} años &nbsp;|&nbsp; {{ $sede }}</div>

                        <div class="cumple-progress">
                            <div class="cumple-progress-bar" style="width: {{ $pct }}%;"></div>
                        </div>

                        <div class="cumple-card-dept">
                            <i class="bi bi-building me-1"></i>{{ $dept }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Footer del box ───────────────────────────── --}}
        <div class="cumple-footer mt-3">
            <i class="bi bi-info-circle text-primary"></i>
            <span>
                <strong>{{ $cumpleaneros->count() }}</strong>
                {{ $cumpleaneros->count() === 1 ? 'persona cumple' : 'personas cumplen' }}
                años en {{ ucfirst($nombreMes) }}.
            </span>
        </div>
    @endif

</div>
@endsection

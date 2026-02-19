@extends('layouts.app')

@section('title', 'Recepción de Permisos')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">📥 Recepción de Permisos</h1>
        <p class="text-gray-500 mt-1">Solicitudes pendientes de aprobación</p>
    </div>
    <span class="mt-2 sm:mt-0 inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-800 font-semibold px-4 py-2 rounded-lg text-sm">
        ⏳ {{ $pendientes->count() }} pendiente(s)
    </span>
</div>

@if($pendientes->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-6xl mb-4">✅</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sin solicitudes pendientes</h3>
        <p class="text-gray-500">Todas las solicitudes han sido procesadas.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($pendientes as $solicitud)
            <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <h3 class="text-lg font-bold text-gray-900">{{ $solicitud->nombre }}</h3>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⏳ Pendiente
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div>
                                <p class="text-gray-500 text-xs uppercase tracking-wider">Grado</p>
                                <p class="font-medium text-gray-800">{{ $solicitud->grado }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase tracking-wider">Nivel</p>
                                <p class="font-medium text-gray-800">{{ $solicitud->nivel }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase tracking-wider">Solicitante</p>
                                <p class="font-medium text-gray-800">{{ $solicitud->quien_solicita }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase tracking-wider">Vía</p>
                                <p class="font-medium text-gray-800">{{ $solicitud->por_via }}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-gray-500 text-xs uppercase tracking-wider">Motivo</p>
                            <p class="text-sm text-gray-700 mt-1">{{ $solicitud->motivo }}</p>
                        </div>
                        <p class="text-xs text-gray-400 mt-3">
                            Creado por {{ $solicitud->creator->name }} · {{ $solicitud->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('permissions.aceptar', $solicitud) }}"
                          onsubmit="return confirm('¿Aceptar la solicitud de {{ $solicitud->nombre }}?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                            ✅ Aceptar
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

@extends('layouts.app')

@section('title', 'Solicitudes de Permisos')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">📋 Solicitudes de Permisos</h1>
        <p class="text-gray-500 mt-1">Listado de todas las solicitudes realizadas</p>
    </div>
    @can('crear_solicitud')
        <a href="{{ route('permissions.create') }}"
           class="mt-4 sm:mt-0 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors">
            ➕ Nueva Solicitud
        </a>
    @endcan
</div>

{{-- Estadísticas --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Solicitudes</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $solicitudes->count() }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-5 bg-yellow-50">
        <p class="text-xs text-yellow-600 uppercase tracking-wider font-semibold">Pendientes</p>
        <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $solicitudes->where('estado', 'pendiente')->count() }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-green-200 p-5 bg-green-50">
        <p class="text-xs text-green-600 uppercase tracking-wider font-semibold">Aceptadas</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $solicitudes->where('estado', 'aceptado')->count() }}</p>
    </div>
</div>

@if($solicitudes->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sin solicitudes</h3>
        <p class="text-gray-500 mb-6">Aún no se han creado solicitudes de permiso.</p>
        @can('crear_solicitud')
            <a href="{{ route('permissions.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-colors">
                ➕ Crear Primera Solicitud
            </a>
        @endcan
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">No.</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Grado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nivel</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Motivo</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Solicitante</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Vía</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($solicitudes as $solicitud)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $solicitud->id }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $solicitud->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $solicitud->grado }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $solicitud->nivel }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $solicitud->motivo }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $solicitud->quien_solicita }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $solicitud->por_via }}</td>
                            <td class="px-6 py-4">
                                @if($solicitud->estado === 'pendiente')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ Pendiente
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Aceptado
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $solicitud->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

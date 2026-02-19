@extends('layouts.app')

@section('title', 'Permisos Aceptados')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">✅ Permisos Aceptados</h1>
        <p class="text-gray-500 mt-1">Listado de permisos aprobados — {{ $aceptados->count() }} total</p>
    </div>
    @can('exportar_permisos')
        <a href="{{ route('permissions.exportar') }}"
           class="mt-4 sm:mt-0 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors">
            📥 Descargar Excel
        </a>
    @endcan
</div>

@if($aceptados->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sin permisos aceptados</h3>
        <p class="text-gray-500">Aún no se han aceptado solicitudes de permiso.</p>
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
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aceptado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($aceptados as $permiso)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $permiso->id }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $permiso->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $permiso->grado }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $permiso->nivel }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $permiso->motivo }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $permiso->quien_solicita }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $permiso->por_via }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $permiso->accepted_at ? $permiso->accepted_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $permiso->acceptor ? $permiso->acceptor->name : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

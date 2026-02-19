@extends('layouts.app')

@section('title', 'Asistencia Personal del Día - Asistencia QR')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">👔 Asistencia del Personal</h1>
        <p class="text-gray-500 mt-1">
            {{ \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3">
        <a href="{{ route('staff-attendance.scan') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors">
            📷 Escanear QR
        </a>
    </div>
</div>

{{-- Filtro por fecha --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('staff-attendance.index') }}" class="flex flex-col sm:flex-row items-end gap-3">
        <div class="w-full sm:w-auto">
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por fecha</label>
            <input type="date"
                   name="date"
                   id="date"
                   value="{{ $date }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </div>
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-5 rounded-lg text-sm transition-colors">
            Buscar
        </button>
    </form>
</div>

{{-- Estadísticas --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Personal</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStaff }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-green-200 p-5 bg-green-50">
        <p class="text-xs text-green-600 uppercase tracking-wider font-semibold">Presentes</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $presentes }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-red-200 p-5 bg-red-50">
        <p class="text-xs text-red-600 uppercase tracking-wider font-semibold">Ausentes</p>
        <p class="text-3xl font-bold text-red-500 mt-1">{{ $ausentes }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-5 bg-yellow-50">
        <p class="text-xs text-yellow-600 uppercase tracking-wider font-semibold">Sin Registro</p>
        <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $sinRegistro }}</p>
    </div>
</div>

{{-- Tabla de asistencia --}}
@if($staffList->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No hay personal registrado</h3>
        <p class="text-gray-500 mb-6">Registre personal primero para comenzar a tomar asistencia.</p>
        <a href="{{ route('staff.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-colors">
            ➕ Registrar Personal
        </a>
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Hora</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($staffList as $item)
                        <tr class="hover:bg-gray-50 transition-colors
                            @if($item->status === 'ausente') bg-red-50 @elseif($item->status === 'sin_registro') bg-yellow-50/50 @endif">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $item->staff->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->staff->role }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($item->status === 'presente')
                                    {{ $item->check_in_time }}
                                @elseif($item->status === 'ausente')
                                    <span class="text-red-400 italic">—</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item->status === 'presente')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Presente
                                    </span>
                                @elseif($item->status === 'ausente')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ❌ Ausente
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⚪ Sin Registro
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item->status !== 'ausente')
                                    <form method="POST" action="{{ route('staff-attendance.absent') }}" class="inline"
                                          onsubmit="return confirm('¿Marcar a {{ $item->staff->name }} como ausente?')">
                                        @csrf
                                        <input type="hidden" name="staff_id" value="{{ $item->staff->id }}">
                                        <input type="hidden" name="date" value="{{ $date }}">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                            ❌ Marcar Ausente
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">Ya marcado</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Botón para marcar a todos sin registro como ausentes --}}
    @if($sinRegistro > 0)
        <div class="mt-4 flex justify-end">
            <form method="POST" action="{{ route('staff-attendance.absent.all') }}"
                  onsubmit="return confirm('¿Marcar a los {{ $sinRegistro }} miembros sin registro como ausentes?')">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors">
                    ❌ Marcar Todos Sin Registro como Ausentes ({{ $sinRegistro }})
                </button>
            </form>
        </div>
    @endif
@endif
@endsection

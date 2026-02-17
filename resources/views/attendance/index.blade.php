@extends('layouts.app')

@section('title', 'Asistencia del Día - Asistencia QR')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">📊 Asistencia del Día</h1>
        <p class="text-gray-500 mt-1">
            {{ \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </p>
    </div>
    <a href="{{ route('attendance.scan') }}"
       class="mt-4 sm:mt-0 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors">
        📷 Escanear QR
    </a>
</div>

{{-- Filtro por fecha --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-col sm:flex-row items-end gap-3">
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
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Presentes</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $attendances->count() }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Alumnos</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStudents }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Ausentes</p>
        <p class="text-3xl font-bold text-red-500 mt-1">{{ $totalStudents - $attendances->count() }}</p>
    </div>
</div>

{{-- Tabla de asistencia --}}
@if($attendances->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sin registros de asistencia</h3>
        <p class="text-gray-500 mb-6">Aún no se ha registrado asistencia para esta fecha.</p>
        <a href="{{ route('attendance.scan') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-colors">
            📷 Comenzar a Escanear
        </a>
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Alumno</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Hora de Entrada</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($attendances as $attendance)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $attendance->student->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $attendance->student->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $attendance->check_in_time }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✅ Presente
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

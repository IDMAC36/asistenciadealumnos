@extends('layouts.app')

@section('title', 'Reporte Mensual - Alumnos')

@section('content')
<div class="max-w-full mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📋 Reporte Mensual — Alumnos</h1>
            <p class="text-gray-500 text-sm mt-1">Asistencia general agrupada por grado</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-2">
            <a href="{{ route('attendance.monthly-report', ['month' => $prevMonth]) }}"
               class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">
                ← Anterior
            </a>
            <span class="text-sm font-semibold text-gray-700 capitalize px-3">{{ $monthLabel }}</span>
            <a href="{{ route('attendance.monthly-report', ['month' => $nextMonth]) }}"
               class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">
                Siguiente →
            </a>
            <a href="{{ route('attendance.monthly-report.export', ['month' => $month]) }}"
               class="px-3 py-2 bg-green-600 hover:bg-green-500 text-white text-sm rounded-lg transition-colors flex items-center gap-1">
                📥 Excel
            </a>
        </div>
    </div>

    {{-- Selector de mes --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('attendance.monthly-report') }}" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="w-full sm:w-auto">
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                <input type="month" name="month" id="month" value="{{ $month }}"
                       class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-5 rounded-lg text-sm transition-colors">
                Consultar
            </button>
        </form>
    </div>

    {{-- Leyenda --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4 text-xs text-gray-600">
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-green-500 inline-block"></span> Presente</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-red-500 inline-block"></span> Ausente</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-amber-400 inline-block"></span> Con Permiso</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-gray-200 inline-block"></span> Sin registro</span>
            <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-white border border-gray-300 inline-block"></span> Futuro</span>
        </div>
    </div>

    {{-- Tablas por grado --}}
    @forelse($reportData as $grado => $students)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-indigo-600 px-5 py-3">
            <h2 class="text-white font-bold text-sm">📚 {{ $grado }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="sticky left-0 z-10 bg-gray-50 px-3 py-2 text-left text-gray-600 font-semibold border-b border-r border-gray-200 min-w-[180px]">
                            Nombre
                        </th>
                        @foreach($weekdays as $day)
                            <th class="px-1 py-2 text-center text-gray-500 font-medium border-b border-gray-200 min-w-[32px]"
                                title="{{ $day->locale('es')->isoFormat('dddd D [de] MMMM') }}">
                                <div class="leading-tight">
                                    <span class="block text-[10px] text-gray-400">{{ mb_substr($day->locale('es')->isoFormat('ddd'), 0, 3) }}</span>
                                    <span class="block font-bold">{{ $day->day }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($students as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="sticky left-0 z-10 bg-white px-3 py-2 font-medium text-gray-900 border-r border-gray-200 whitespace-nowrap">
                            {{ $row->student->name }}
                        </td>
                        @foreach($weekdays as $day)
                            @php
                                $dateStr = $day->format('Y-m-d');
                                $status = $row->days[$dateStr] ?? null;
                            @endphp
                            <td class="px-0.5 py-1 text-center">
                                @if($status === 'presente')
                                    <div class="w-6 h-6 mx-auto rounded bg-green-500" title="Presente - {{ $day->format('d/m') }}"></div>
                                @elseif($status === 'ausente')
                                    <div class="w-6 h-6 mx-auto rounded bg-red-500" title="Ausente - {{ $day->format('d/m') }}"></div>
                                @elseif($status === 'permiso')
                                    <div class="w-6 h-6 mx-auto rounded bg-amber-400" title="Permiso - {{ $day->format('d/m') }}"></div>
                                @elseif($status === 'sin_registro')
                                    <div class="w-6 h-6 mx-auto rounded bg-gray-200" title="Sin registro - {{ $day->format('d/m') }}"></div>
                                @else
                                    <div class="w-6 h-6 mx-auto rounded bg-white border border-gray-200" title="{{ $day->format('d/m') }}"></div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="text-5xl mb-4">📭</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No hay alumnos registrados</h3>
            <p class="text-gray-500 text-sm">Registra alumnos para ver el reporte mensual.</p>
        </div>
    @endforelse
</div>
@endsection

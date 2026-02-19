@extends('layouts.app')

@section('title', 'Historial de Asistencia - Personal')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">📅 Historial de Asistencia — Personal</h1>
        <p class="text-gray-500 text-sm mt-1">Visualiza la asistencia mensual por miembro del personal</p>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('staff-attendance.history') }}" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-1">Personal</label>
                <select name="staff_id" id="staff_id" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">-- Seleccionar personal --</option>
                    @foreach($staffMembers as $m)
                        <option value="{{ $m->id }}" {{ $staffId == $m->id ? 'selected' : '' }}>
                            {{ $m->name }} — {{ $m->role }} ({{ $m->dpi }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-44">
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                <input type="month" name="month" id="month" value="{{ $month }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors whitespace-nowrap">
                🔍 Consultar
            </button>
        </form>
    </div>

    @if($staff)
    {{-- Info del personal + navegación mes --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-indigo-600 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-white">{{ $staff->name }}</h2>
                <p class="text-indigo-200 text-sm">{{ $staff->role }} · DPI: {{ $staff->dpi }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('staff-attendance.history', ['staff_id' => $staff->id, 'month' => $prevMonth]) }}"
                   class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-400 text-white text-sm rounded-lg transition-colors">
                    ← Anterior
                </a>
                <span class="text-white font-semibold text-sm capitalize px-3">{{ $monthLabel }}</span>
                <a href="{{ route('staff-attendance.history', ['staff_id' => $staff->id, 'month' => $nextMonth]) }}"
                   class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-400 text-white text-sm rounded-lg transition-colors">
                    Siguiente →
                </a>
            </div>
        </div>

        {{-- Estadísticas --}}
        <div class="grid grid-cols-3 gap-4 p-5 border-b border-gray-200">
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['presente'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Presentes</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ $stats['ausente'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Ausentes</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-400">{{ $stats['sin_registro'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Sin Registro</div>
            </div>
        </div>

        {{-- Calendario --}}
        <div class="p-5">
            {{-- Cabecera días de la semana --}}
            <div class="grid grid-cols-7 gap-1 mb-2">
                @foreach(['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $dayName)
                    <div class="text-center text-xs font-semibold text-gray-500 py-1">{{ $dayName }}</div>
                @endforeach
            </div>

            {{-- Días del mes --}}
            <div class="grid grid-cols-7 gap-1">
                {{-- Espacios vacíos antes del primer día --}}
                @if(count($calendar) > 0)
                    @for($i = 0; $i < $calendar[0]->dayOfWeek; $i++)
                        <div></div>
                    @endfor
                @endif

                @foreach($calendar as $day)
                    @php
                        if ($day->isWeekend) {
                            $bgClass = 'bg-gray-100 text-gray-400';
                            $icon = '';
                        } elseif ($day->isFuture) {
                            $bgClass = 'bg-gray-50 text-gray-300';
                            $icon = '';
                        } elseif ($day->status === 'presente') {
                            $bgClass = 'bg-green-100 text-green-800 ring-1 ring-green-300';
                            $icon = '✅';
                        } elseif ($day->status === 'ausente') {
                            $bgClass = 'bg-red-100 text-red-800 ring-1 ring-red-300';
                            $icon = '❌';
                        } else {
                            $bgClass = 'bg-gray-50 text-gray-400';
                            $icon = '—';
                        }
                    @endphp
                    <div class="relative rounded-lg p-2 text-center {{ $bgClass }} min-h-[60px] flex flex-col items-center justify-center transition-all hover:scale-105 cursor-default"
                         title="{{ $day->date }}{{ $day->checkInTime ? ' — Entrada: ' . $day->checkInTime : '' }}">
                        <span class="text-xs font-bold">{{ $day->day }}</span>
                        @if($icon)
                            <span class="text-sm mt-0.5">{{ $icon }}</span>
                        @endif
                        @if($day->checkInTime && $day->status === 'presente')
                            <span class="text-[10px] opacity-70">{{ \Carbon\Carbon::parse($day->checkInTime)->format('H:i') }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Leyenda --}}
        <div class="px-5 pb-5">
            <div class="flex flex-wrap gap-4 text-xs text-gray-600">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-200 ring-1 ring-green-300 inline-block"></span> Presente</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-200 ring-1 ring-red-300 inline-block"></span> Ausente</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-100 inline-block"></span> Fin de semana</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-50 ring-1 ring-gray-200 inline-block"></span> Sin registro</span>
            </div>
        </div>
    </div>

    {{-- Listado detallado --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">📋 Detalle del mes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Día</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hora Entrada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $dayNames = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
                    @endphp
                    @foreach($calendar as $day)
                        @if(!$day->isWeekend && !$day->isFuture)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $dayNames[$day->dayOfWeek] }}</td>
                            <td class="px-6 py-3">
                                @if($day->status === 'presente')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✅ Presente</span>
                                @elseif($day->status === 'ausente')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">❌ Ausente</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">— Sin registro</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ $day->checkInTime ? \Carbon\Carbon::parse($day->checkInTime)->format('h:i A') : '—' }}
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="text-5xl mb-4">📊</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Selecciona un miembro del personal</h3>
            <p class="text-gray-500 text-sm">Elige un miembro del personal y un mes para ver su historial de asistencia.</p>
        </div>
    @endif
</div>
@endsection

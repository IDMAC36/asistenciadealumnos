@extends('layouts.app')

@section('title', 'Alumnos - Asistencia QR')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">👨‍🎓 Listado de Alumnos</h1>
        <p class="text-gray-500 mt-1">Total: {{ $students->count() }} alumnos registrados</p>
    </div>
    <a href="{{ route('students.create') }}"
       class="mt-4 sm:mt-0 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors">
        ➕ Nuevo Alumno
    </a>
</div>

@if($students->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No hay alumnos registrados</h3>
        <p class="text-gray-500 mb-6">Comienza registrando tu primer alumno para generar su código QR.</p>
        <a href="{{ route('students.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-colors">
            ➕ Registrar Alumno
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
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Código Personal</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Grado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($students as $student)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $student->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $student->codigo_personal }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $student->grado }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $student->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('students.show', $student) }}"
                                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
                                        Ver QR
                                    </a>
                                    <a href="{{ route('students.edit', $student) }}"
                                       class="text-amber-600 hover:text-amber-800 text-sm font-medium transition-colors">
                                        Editar
                                    </a>
                                    <form action="{{ route('students.destroy', $student) }}" method="POST"
                                          onsubmit="return confirm('¿Estás seguro de eliminar a {{ $student->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

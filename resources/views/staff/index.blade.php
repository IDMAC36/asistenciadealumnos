@extends('layouts.app')

@section('title', 'Personal - Asistencia QR')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">👔 Personal</h1>
        <p class="text-gray-500 mt-1">{{ $staff->count() }} miembro(s) registrado(s)</p>
    </div>
    <a href="{{ route('staff.create') }}"
       class="mt-4 sm:mt-0 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors">
        ➕ Registrar Personal
    </a>
</div>

@if($staff->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="text-6xl mb-4">👔</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sin personal registrado</h3>
        <p class="text-gray-500 mb-6">Comienza agregando miembros del personal.</p>
        <a href="{{ route('staff.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-colors">
            ➕ Registrar Primer Personal
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
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">DPI</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Registrado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($staff as $member)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ $member->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $member->dpi }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $member->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $member->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('staff.show', $member) }}"
                                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
                                        Ver QR
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('staff.edit', $member) }}"
                                       class="text-amber-600 hover:text-amber-800 text-sm font-medium transition-colors">
                                        Editar
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('staff.destroy', $member) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Eliminar a {{ $member->name }}?')">
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

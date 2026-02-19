@extends('layouts.app')

@section('title', 'Editar Personal - Asistencia QR')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="{{ route('staff.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
            ← Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-yellow-500 px-6 py-5">
            <h1 class="text-xl font-bold text-white">✏️ Editar Personal</h1>
            <p class="text-yellow-100 text-sm mt-1">Modificar información de {{ $staff->name }}</p>
        </div>

        <form method="POST" action="{{ route('staff.update', $staff) }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo *</label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $staff->name) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none @error('name') border-red-500 @enderror"
                       placeholder="Nombre completo del personal">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                <input type="text"
                       name="role"
                       id="role"
                       value="{{ old('role', $staff->role) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none @error('role') border-red-500 @enderror"
                       placeholder="Ej: Docente, Administrativo, Conserje">
                @error('role')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('staff.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-yellow-500 hover:bg-yellow-600 rounded-lg shadow-sm transition-colors">
                    💾 Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

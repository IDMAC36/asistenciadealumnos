@extends('layouts.app')

@section('title', 'Nueva Solicitud de Permiso')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('permissions.solicitudes') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
            ← Volver a solicitudes
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-indigo-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">📝 Nueva Solicitud de Permiso</h1>
            <p class="text-indigo-200 text-sm mt-1">Complete el formulario para solicitar un permiso de estudiante</p>
        </div>

        <form method="POST" action="{{ route('permissions.store') }}" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Estudiante *</label>
                    <input type="text"
                           name="nombre"
                           id="nombre"
                           value="{{ old('nombre') }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('nombre') border-red-500 @enderror"
                           placeholder="Nombre completo del estudiante">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="grado" class="block text-sm font-medium text-gray-700 mb-1">Grado *</label>
                    <input type="text"
                           name="grado"
                           id="grado"
                           value="{{ old('grado') }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('grado') border-red-500 @enderror"
                           placeholder="Ej: 5to">
                    @error('grado')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nivel" class="block text-sm font-medium text-gray-700 mb-1">Nivel *</label>
                    <select name="nivel"
                            id="nivel"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('nivel') border-red-500 @enderror">
                        <option value="">Seleccione...</option>
                        <option value="Primaria" {{ old('nivel') == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                        <option value="Básico" {{ old('nivel') == 'Básico' ? 'selected' : '' }}>Básico</option>
                        <option value="Diversificado" {{ old('nivel') == 'Diversificado' ? 'selected' : '' }}>Diversificado</option>
                    </select>
                    @error('nivel')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">Motivo *</label>
                    <textarea name="motivo"
                              id="motivo"
                              rows="3"
                              required
                              class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('motivo') border-red-500 @enderror"
                              placeholder="Describa el motivo del permiso">{{ old('motivo') }}</textarea>
                    @error('motivo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quien_solicita" class="block text-sm font-medium text-gray-700 mb-1">Quién Solicita *</label>
                    <input type="text"
                           name="quien_solicita"
                           id="quien_solicita"
                           value="{{ old('quien_solicita') }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('quien_solicita') border-red-500 @enderror"
                           placeholder="Ej: Padre de familia">
                    @error('quien_solicita')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="por_via" class="block text-sm font-medium text-gray-700 mb-1">Por Vía *</label>
                    <select name="por_via"
                            id="por_via"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('por_via') border-red-500 @enderror">
                        <option value="">Seleccione...</option>
                        <option value="Presencial" {{ old('por_via') == 'Presencial' ? 'selected' : '' }}>Presencial</option>
                        <option value="Teléfono" {{ old('por_via') == 'Teléfono' ? 'selected' : '' }}>Teléfono</option>
                        <option value="Correo" {{ old('por_via') == 'Correo' ? 'selected' : '' }}>Correo</option>
                        <option value="Nota escrita" {{ old('por_via') == 'Nota escrita' ? 'selected' : '' }}>Nota escrita</option>
                    </select>
                    @error('por_via')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('permissions.solicitudes') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                    📨 Enviar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

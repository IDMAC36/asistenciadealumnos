@extends('layouts.app')

@section('title', 'Registrar Alumno - Asistencia QR')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
            ← Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">➕ Registrar Nuevo Alumno</h1>

        <form action="{{ route('students.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name') }}"
                       required
                       autofocus
                       placeholder="Ej: Juan Pérez García"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="codigo_personal" class="block text-sm font-medium text-gray-700 mb-1">Código Personal</label>
                <input type="text"
                       name="codigo_personal"
                       id="codigo_personal"
                       value="{{ old('codigo_personal') }}"
                       required
                       placeholder="Ej: CP-001"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow @error('codigo_personal') border-red-500 @enderror">
                @error('codigo_personal')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="grado" class="block text-sm font-medium text-gray-700 mb-1">Grado</label>
                <input type="text"
                       name="grado"
                       id="grado"
                       value="{{ old('grado') }}"
                       required
                       placeholder="Ej: 3° A Primaria"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow @error('grado') border-red-500 @enderror">
                @error('grado')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="plan" class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                <select name="plan"
                        id="plan"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow @error('plan') border-red-500 @enderror">
                    <option value="" disabled {{ old('plan') ? '' : 'selected' }}>-- Seleccionar plan --</option>
                    <option value="plan_diario" {{ old('plan') === 'plan_diario' ? 'selected' : '' }}>Plan Diario</option>
                    <option value="plan_fin_de_semana" {{ old('plan') === 'plan_fin_de_semana' ? 'selected' : '' }}>Plan Fin de Semana</option>
                </select>
                @error('plan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-sm transition-colors">
                    Registrar Alumno
                </button>
                <a href="{{ route('students.index') }}"
                   class="text-gray-500 hover:text-gray-700 font-medium py-2.5 px-4 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-700">
            💡 <strong>Nota:</strong> Al registrar el alumno se generará automáticamente un código QR único que podrá usar para registrar su asistencia.
        </p>
    </div>
</div>
@endsection

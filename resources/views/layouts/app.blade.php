<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asistencia QR')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-indigo-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('students.index') }}" class="text-white font-bold text-xl">
                        📋 Asistencia QR
                    </a>
                    <div class="hidden sm:flex items-center gap-4">
                        <a href="{{ route('students.index') }}"
                           class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('students.*') ? 'bg-indigo-700 text-white' : '' }}">
                            👨‍🎓 Alumnos
                        </a>
                        <a href="{{ route('attendance.index') }}"
                           class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('attendance.index') ? 'bg-indigo-700 text-white' : '' }}">
                            📊 Asistencia
                        </a>
                        <a href="{{ route('attendance.scan') }}"
                           class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('attendance.scan') ? 'bg-indigo-700 text-white' : '' }}">
                            📷 Escanear QR
                        </a>
                    </div>
                </div>
            </div>
        </div>
        {{-- Mobile nav --}}
        <div class="sm:hidden border-t border-indigo-500 px-4 py-2 flex gap-2">
            <a href="{{ route('students.index') }}"
               class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-xs font-medium {{ request()->routeIs('students.*') ? 'bg-indigo-700 text-white' : '' }}">
                👨‍🎓 Alumnos
            </a>
            <a href="{{ route('attendance.index') }}"
               class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-xs font-medium {{ request()->routeIs('attendance.index') ? 'bg-indigo-700 text-white' : '' }}">
                📊 Asistencia
            </a>
            <a href="{{ route('attendance.scan') }}"
               class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-xs font-medium {{ request()->routeIs('attendance.scan') ? 'bg-indigo-700 text-white' : '' }}">
                📷 Escanear
            </a>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <span>✅ {{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <span>❌ {{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">&times;</button>
            </div>
        </div>
    @endif

    {{-- Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

</body>
</html>

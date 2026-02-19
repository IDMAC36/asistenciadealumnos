<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asistencia QR')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Sidebar transition */
        .sidebar { transition: transform 0.3s ease; }
        .sidebar.closed { transform: translateX(-100%); }
        @media (min-width: 1024px) {
            .sidebar { transform: translateX(0) !important; }
        }
        .sidebar-overlay { transition: opacity 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    @auth
    {{-- Mobile top bar --}}
    <div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-indigo-600 shadow-lg h-14 flex items-center px-4">
        <button onclick="toggleSidebar()" class="text-white hover:bg-indigo-700 p-2 rounded-lg transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <span class="text-white font-bold text-lg ml-3">📋 Asistencia QR</span>
    </div>

    {{-- Sidebar overlay (mobile only) --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden sidebar-overlay" onclick="toggleSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar" class="sidebar closed lg:!transform-none fixed top-0 left-0 z-50 h-full w-64 bg-indigo-700 shadow-xl flex flex-col overflow-y-auto">
        {{-- Logo --}}
        <div class="px-5 py-5 flex items-center justify-between border-b border-indigo-600">
            <a href="{{ route('students.index') }}" class="text-white font-bold text-lg flex items-center gap-2">
                📋 Asistencia QR
            </a>
            <button onclick="toggleSidebar()" class="lg:hidden text-indigo-300 hover:text-white p-1 rounded transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation links --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            {{-- Sección: Alumnos --}}
            <p class="px-3 text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Alumnos</p>

            <a href="{{ route('students.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('students.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                <span class="text-lg">👨‍🎓</span> Alumnos
            </a>
            <a href="{{ route('attendance.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('attendance.index') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                <span class="text-lg">📊</span> Asistencia
            </a>
            <a href="{{ route('attendance.scan') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('attendance.scan') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                <span class="text-lg">📷</span> Escanear QR
            </a>
            <a href="{{ route('attendance.history') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('attendance.history') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                <span class="text-lg">📅</span> Historial
            </a>

            {{-- Sección: Personal --}}
            <p class="px-3 text-xs font-semibold text-indigo-300 uppercase tracking-wider mt-5 mb-2">Personal</p>

            <a href="{{ route('staff.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('staff.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                <span class="text-lg">👔</span> Personal
            </a>
            <a href="{{ route('staff-attendance.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('staff-attendance.index') || request()->routeIs('staff-attendance.scan') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                <span class="text-lg">📊</span> Asist. Personal
            </a>
            <a href="{{ route('staff-attendance.history') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('staff-attendance.history') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                <span class="text-lg">📅</span> Historial Personal
            </a>

            {{-- Sección: Permisos --}}
            @hasanyrole('secretaria|admin|operativo')
            <p class="px-3 text-xs font-semibold text-indigo-300 uppercase tracking-wider mt-5 mb-2">Permisos</p>
            @endhasanyrole

            @hasanyrole('secretaria|admin')
                <a href="{{ route('permissions.solicitudes') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('permissions.solicitudes') || request()->routeIs('permissions.create') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                    <span class="text-lg">📝</span> Solicitudes
                </a>
            @endhasanyrole

            @hasrole('admin')
                <a href="{{ route('permissions.pendientes') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('permissions.pendientes') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                    <span class="text-lg">📥</span> Recepción
                </a>
            @endhasrole

            @hasanyrole('secretaria|admin|operativo')
                <a href="{{ route('permissions.aceptados') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('permissions.aceptados') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                    <span class="text-lg">✅</span> Permisos
                </a>
            @endhasanyrole

            {{-- Sección: Administración --}}
            @hasrole('admin')
            <p class="px-3 text-xs font-semibold text-indigo-300 uppercase tracking-wider mt-5 mb-2">Administración</p>

                <a href="{{ route('users.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600 hover:text-white' }}">
                    <span class="text-lg">👥</span> Usuarios
                </a>
            @endhasrole
        </nav>

        {{-- User info & logout --}}
        <div class="border-t border-indigo-600 px-4 py-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-indigo-300 text-xs capitalize">{{ auth()->user()->roles->first()?->name ?? 'usuario' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 text-indigo-200 hover:text-white hover:bg-indigo-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                    🚪 Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>
    @endauth

    {{-- Main content wrapper --}}
    <div class="@auth lg:ml-64 @endauth">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 @auth mt-16 lg:mt-4 @else mt-4 @endauth">
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span>✅ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">&times;</button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 @auth mt-16 lg:mt-4 @else mt-4 @endauth">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span>❌ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">&times;</button>
                </div>
            </div>
        @endif

        {{-- Content --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 @auth pt-18 lg:pt-8 pb-8 @else py-8 @endauth">
            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('closed');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>

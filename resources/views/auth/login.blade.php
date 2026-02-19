<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Asistencia QR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">

    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-600">📋 Asistencia QR</h1>
            <p class="text-gray-500 mt-2">Inicia sesión para continuar</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    @foreach($errors->all() as $error)
                        <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="correo@ejemplo.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Recordarme</label>
                </div>

                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Iniciar Sesión
                </button>
            </form>
        </div>

        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-blue-700 mb-2">👥 Usuarios de prueba (contraseña: password)</p>
            <div class="space-y-1 text-xs text-blue-600">
                <p><strong>Admin:</strong> admin@asistencia.com</p>
                <p><strong>Secretaria:</strong> secretaria@asistencia.com</p>
                <p><strong>Operativo:</strong> operativo@asistencia.com</p>
            </div>
        </div>
    </div>

</body>
</html>

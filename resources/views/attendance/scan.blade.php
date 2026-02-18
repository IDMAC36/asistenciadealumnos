@extends('layouts.app')

@section('title', 'Escanear QR - Asistencia QR')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('attendance.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
            ← Volver a asistencia
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-green-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">📷 Escanear Código QR</h1>
            <p class="text-green-200 text-sm mt-1">Apunta la cámara al código QR del alumno</p>
        </div>

        <div class="p-6">
            {{-- Scanner container --}}
            <div id="reader" class="mx-auto rounded-lg overflow-hidden" style="max-width: 500px;"></div>

            {{-- Result area --}}
            <div id="scan-result" class="mt-6 hidden">
                <div id="scan-message" class="p-4 rounded-lg text-center font-semibold"></div>
            </div>

            {{-- Success modal overlay --}}
            <div id="success-overlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-2xl shadow-2xl p-8 mx-4 max-w-sm w-full text-center transform transition-all">
                    <div class="text-6xl mb-4">✅</div>
                    <h2 class="text-2xl font-bold text-green-600 mb-2">¡Asistencia Exitosa!</h2>
                    <p id="success-student-name" class="text-lg font-semibold text-gray-800"></p>
                    <p id="success-student-time" class="text-sm text-gray-500 mt-1"></p>
                    <div class="mt-6 h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div id="success-progress" class="h-full bg-green-500 rounded-full transition-all duration-[3000ms] ease-linear" style="width: 100%;"></div>
                    </div>
                </div>
            </div>

            {{-- Already registered modal overlay --}}
            <div id="already-overlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-2xl shadow-2xl p-8 mx-4 max-w-sm w-full text-center transform transition-all">
                    <div class="text-6xl mb-4">⚠️</div>
                    <h2 class="text-2xl font-bold text-amber-600 mb-2">Ya ha sido registrado</h2>
                    <p id="already-student-name" class="text-lg font-semibold text-gray-800"></p>
                    <p id="already-student-time" class="text-sm text-gray-500 mt-1"></p>
                    <div class="mt-6 h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div id="already-progress" class="h-full bg-amber-500 rounded-full transition-all duration-[3000ms] ease-linear" style="width: 100%;"></div>
                    </div>
                </div>
            </div>

            {{-- Scan log --}}
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">📝 Registro de escaneos de hoy</h3>
                <div id="scan-log" class="space-y-2">
                    <p class="text-sm text-gray-400 text-center py-4" id="empty-log">Aún no se han escaneado códigos.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- html5-qrcode library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    let isProcessing = false;

    function onScanSuccess(decodedText, decodedResult) {
        // Evitar escaneos múltiples simultáneos
        if (isProcessing) return;
        isProcessing = true;

        const resultDiv = document.getElementById('scan-result');
        const messageDiv = document.getElementById('scan-message');
        resultDiv.classList.remove('hidden');
        messageDiv.textContent = '⏳ Procesando...';
        messageDiv.className = 'p-4 rounded-lg text-center font-semibold bg-blue-50 text-blue-700';

        fetch('{{ route("attendance.mark") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ qr_code: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.textContent = data.message;
                messageDiv.className = 'p-4 rounded-lg text-center font-semibold bg-green-50 text-green-700 border border-green-200';
                addToLog(data.student.name, data.student.time, true);
                showSuccessOverlay(data.student.name, data.student.time);
            } else if (data.already_registered) {
                messageDiv.textContent = data.message;
                messageDiv.className = 'p-4 rounded-lg text-center font-semibold bg-amber-50 text-amber-700 border border-amber-200';
                showAlreadyOverlay(data.student.name, data.student.time);
            } else {
                messageDiv.textContent = data.message;
                messageDiv.className = 'p-4 rounded-lg text-center font-semibold bg-red-50 text-red-700 border border-red-200';
            }

            // Esperar 3 segundos antes de permitir otro escaneo
            setTimeout(() => {
                isProcessing = false;
                messageDiv.textContent = '📷 Listo para escanear otro código...';
                messageDiv.className = 'p-4 rounded-lg text-center font-semibold bg-gray-50 text-gray-500';
            }, 3000);
        })
        .catch(error => {
            messageDiv.textContent = '❌ Error de conexión. Intenta de nuevo.';
            messageDiv.className = 'p-4 rounded-lg text-center font-semibold bg-red-50 text-red-700 border border-red-200';
            isProcessing = false;
        });
    }

    function addToLog(name, time, success) {
        const log = document.getElementById('scan-log');
        const emptyLog = document.getElementById('empty-log');
        if (emptyLog) emptyLog.remove();

        const entry = document.createElement('div');
        entry.className = 'flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-4 py-2';
        entry.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="text-green-600">✅</span>
                <span class="text-sm font-medium text-gray-900">${name}</span>
            </div>
            <span class="text-xs text-gray-500">${time}</span>
        `;
        log.prepend(entry);
    }

    function showSuccessOverlay(name, time) {
        const overlay = document.getElementById('success-overlay');
        const progressBar = document.getElementById('success-progress');
        document.getElementById('success-student-name').textContent = name;
        document.getElementById('success-student-time').textContent = 'Hora: ' + time;

        // Show overlay
        overlay.classList.remove('hidden');

        // Animate progress bar
        progressBar.style.width = '100%';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                progressBar.style.width = '0%';
            });
        });

        // Hide after 3 seconds
        setTimeout(() => {
            overlay.classList.add('hidden');
            progressBar.style.width = '100%';
        }, 3000);
    }

    function showAlreadyOverlay(name, time) {
        const overlay = document.getElementById('already-overlay');
        const progressBar = document.getElementById('already-progress');
        document.getElementById('already-student-name').textContent = name;
        document.getElementById('already-student-time').textContent = 'Registrado a las ' + time;

        overlay.classList.remove('hidden');

        progressBar.style.width = '100%';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                progressBar.style.width = '0%';
            });
        });

        setTimeout(() => {
            overlay.classList.add('hidden');
            progressBar.style.width = '100%';
        }, 3000);
    }

    // Inicializar el escáner
    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true,
            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
        },
        false
    );
    html5QrcodeScanner.render(onScanSuccess);
</script>

<style>
    /* Estilos personalizados para el escáner */
    #reader {
        border: none !important;
    }
    #reader__scan_region {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    #reader__dashboard_section_csr button {
        background-color: #4f46e5 !important;
        color: white !important;
        border: none !important;
        padding: 8px 16px !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        cursor: pointer !important;
    }
    #reader__dashboard_section_csr select {
        padding: 8px !important;
        border-radius: 8px !important;
        border: 1px solid #d1d5db !important;
    }
</style>
@endsection

@extends('layouts.app')

@section('title', $staff->name . ' - Asistencia QR')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('staff.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
            ← Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="bg-indigo-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">{{ $staff->name }}</h1>
            <p class="text-indigo-200 text-sm mt-1">DPI: {{ $staff->dpi }} · {{ $staff->role }}</p>
        </div>

        {{-- QR Code --}}
        <div class="p-6 flex flex-col items-center">
            <div id="qr-container" class="bg-white border-2 border-gray-200 rounded-xl p-6 shadow-sm">
                {!! $qrCode !!}
            </div>
            <p class="text-xs text-gray-400 mt-3 font-mono">{{ $staff->qr_code }}</p>

            {{-- Download PNG button --}}
            <button onclick="downloadQrAsPng()"
                    class="mt-4 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors cursor-pointer">
                📥 Descargar QR como PNG
            </button>
        </div>

        {{-- Info --}}
        <div class="border-t border-gray-100 px-6 py-4">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs text-gray-500 uppercase tracking-wider">Nombre</dt>
                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $staff->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase tracking-wider">DPI</dt>
                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $staff->dpi }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase tracking-wider">Rol</dt>
                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $staff->role }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase tracking-wider">Registrado el</dt>
                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $staff->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase tracking-wider">Asistencias totales</dt>
                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $staff->attendances()->count() }}</dd>
                </div>
            </dl>
        </div>

        {{-- Actions --}}
        <div class="border-t border-gray-100 px-6 py-4 flex items-center gap-3">
            <a href="{{ route('staff.edit', $staff) }}"
               class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                ✏️ Editar
            </a>
            <form action="{{ route('staff.destroy', $staff) }}" method="POST"
                  onsubmit="return confirm('¿Estás seguro de eliminar a {{ $staff->name }}?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                    🗑️ Eliminar
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function downloadQrAsPng() {
    const svgElement = document.querySelector('#qr-container svg');
    if (!svgElement) {
        alert('No se encontró el código QR.');
        return;
    }

    const svgClone = svgElement.cloneNode(true);
    svgClone.setAttribute('width', '400');
    svgClone.setAttribute('height', '400');

    const svgData = new XMLSerializer().serializeToString(svgClone);
    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);

    const img = new Image();
    img.onload = function () {
        const padding = 40;
        const textHeight = 60;
        const canvas = document.createElement('canvas');
        canvas.width = 400 + (padding * 2);
        canvas.height = 400 + (padding * 2) + textHeight;
        const ctx = canvas.getContext('2d');

        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Draw QR
        ctx.drawImage(img, padding, padding, 400, 400);

        // Draw staff name below
        ctx.fillStyle = '#1b1b18';
        ctx.font = 'bold 18px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(@json($staff->name), canvas.width / 2, 400 + padding + 28);

        // Draw role
        ctx.fillStyle = '#706f6c';
        ctx.font = '14px Arial, sans-serif';
        ctx.fillText(@json($staff->role), canvas.width / 2, 400 + padding + 50);

        // Trigger download
        const link = document.createElement('a');
        link.download = @json('qr-staff-' . Str::slug($staff->name) . '.png');
        link.href = canvas.toDataURL('image/png');
        link.click();

        URL.revokeObjectURL(url);
    };
    img.src = url;
}
</script>
@endsection

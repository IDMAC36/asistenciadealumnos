<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use Illuminate\Http\Request;
use App\Exports\PermissionRequestsExport;
use Maatwebsite\Excel\Facades\Excel;

class PermissionRequestController extends Controller
{
    /**
     * Formulario de nueva solicitud (Secretaria, Admin).
     */
    public function create()
    {
        return view('permissions.create');
    }

    /**
     * Guardar solicitud (Secretaria, Admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:255',
            'grado'           => 'required|string|max:100',
            'nivel'           => 'required|string|max:100',
            'motivo'          => 'required|string|max:1000',
            'quien_solicita'  => 'required|string|max:255',
            'por_via'         => 'required|string|max:255',
        ]);

        $validated['estado']     = 'pendiente';
        $validated['created_by'] = auth()->id();

        PermissionRequest::create($validated);

        return redirect()->route('permissions.solicitudes')
            ->with('success', 'Solicitud de permiso enviada correctamente.');
    }

    /**
     * Listado de solicitudes (Secretaria: ve todas; Admin: ve todas).
     */
    public function solicitudes()
    {
        $solicitudes = PermissionRequest::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('permissions.solicitudes', compact('solicitudes'));
    }

    /**
     * Recepción: solo solicitudes pendientes (Admin).
     */
    public function pendientes()
    {
        $pendientes = PermissionRequest::pendiente()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('permissions.pendientes', compact('pendientes'));
    }

    /**
     * Aceptar una solicitud (Admin).
     */
    public function aceptar(PermissionRequest $permissionRequest)
    {
        $permissionRequest->update([
            'estado'      => 'aceptado',
            'accepted_at' => now(),
            'accepted_by' => auth()->id(),
        ]);

        return redirect()->route('permissions.pendientes')
            ->with('success', 'Solicitud de ' . $permissionRequest->nombre . ' aceptada.');
    }

    /**
     * Lista de permisos aceptados (Admin, Operativo).
     */
    public function aceptados()
    {
        $aceptados = PermissionRequest::aceptado()
            ->with(['creator', 'acceptor'])
            ->orderBy('accepted_at', 'desc')
            ->get();

        return view('permissions.aceptados', compact('aceptados'));
    }

    /**
     * Exportar permisos aceptados a Excel (Admin, Operativo).
     */
    public function exportar()
    {
        return Excel::download(
            new PermissionRequestsExport,
            'permisos_aceptados_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}

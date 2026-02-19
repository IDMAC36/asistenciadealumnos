<?php

namespace App\Exports;

use App\Models\PermissionRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PermissionRequestsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return PermissionRequest::aceptado()
            ->with('creator')
            ->orderBy('accepted_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nombre del Estudiante',
            'Grado',
            'Nivel',
            'Motivo',
            'Quién Solicita',
            'Por Vía',
            'Estado',
            'Fecha de Solicitud',
            'Fecha de Aceptación',
        ];
    }

    public function map($request): array
    {
        return [
            $request->id,
            $request->nombre,
            $request->grado,
            $request->nivel,
            $request->motivo,
            $request->quien_solicita,
            $request->por_via,
            ucfirst($request->estado),
            $request->created_at->format('d/m/Y H:i'),
            $request->accepted_at ? $request->accepted_at->format('d/m/Y H:i') : '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

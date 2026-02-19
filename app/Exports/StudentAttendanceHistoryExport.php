<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\PermissionRequest;
use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentAttendanceHistoryExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected Student $student;
    protected string $month;

    public function __construct(Student $student, string $month)
    {
        $this->student = $student;
        $this->month = $month;
    }

    public function array(): array
    {
        $startOfMonth = Carbon::parse($this->month . '-01');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now()->startOfDay();

        $attendances = Attendance::where('student_id', $this->student->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

        $permisos = PermissionRequest::where('nombre', $this->student->name)
            ->where('estado', 'aceptado')
            ->whereNotNull('accepted_at')
            ->whereBetween('created_at', [$startOfMonth->startOfDay(), $endOfMonth->endOfDay()])
            ->get()
            ->keyBy(fn($p) => Carbon::parse($p->created_at)->format('Y-m-d'));

        $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $rows = [];

        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            $dateStr = $day->format('Y-m-d');
            $dayOfWeek = $day->dayOfWeek;
            $isWeekend = in_array($dayOfWeek, [0, 6]);
            $isFuture = $day->gt($today);

            if ($isWeekend) {
                continue; // Skip weekends
            }

            if ($isFuture) {
                $status = '—';
                $hora = '';
                $obs = '';
            } elseif (isset($permisos[$dateStr])) {
                $status = 'Permiso';
                $hora = '';
                $obs = $permisos[$dateStr]->motivo;
            } elseif (isset($attendances[$dateStr])) {
                $status = ucfirst($attendances[$dateStr]->status);
                $hora = $attendances[$dateStr]->check_in_time
                    ? Carbon::parse($attendances[$dateStr]->check_in_time)->format('h:i A')
                    : '';
                $obs = '';
            } else {
                $status = 'Sin registro';
                $hora = '';
                $obs = '';
            }

            $rows[] = [
                $day->format('d/m/Y'),
                $dayNames[$dayOfWeek],
                $status,
                $hora,
                $obs,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Día',
            'Estado',
            'Hora Entrada',
            'Observación',
        ];
    }

    public function title(): string
    {
        return 'Historial ' . $this->student->name;
    }

    public function styles(Worksheet $sheet): array
    {
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}

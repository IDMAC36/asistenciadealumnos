<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyAttendanceExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected string $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public function array(): array
    {
        $students = Student::with(['attendances' => function ($query) {
            $query->whereDate('date', $this->date);
        }])->orderBy('name')->get();

        $rows = [];
        $i = 1;

        foreach ($students as $student) {
            $attendance = $student->attendances->first();
            $status = $attendance ? $attendance->status : 'sin_registro';

            $statusLabel = match ($status) {
                'presente' => 'Presente',
                'ausente' => 'Ausente',
                default => 'Sin registro',
            };

            $checkIn = $attendance && $attendance->check_in_time
                ? Carbon::parse($attendance->check_in_time)->format('h:i A')
                : '—';

            $rows[] = [
                $i++,
                $student->name,
                $student->codigo_personal,
                $student->grado,
                $student->plan === 'plan_diario' ? 'Diario' : 'Fin de Semana',
                $statusLabel,
                $checkIn,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            '#',
            'Nombre',
            'Código Personal',
            'Grado',
            'Plan',
            'Estado',
            'Hora Entrada',
        ];
    }

    public function title(): string
    {
        return 'Asistencia ' . Carbon::parse($this->date)->format('d-m-Y');
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}

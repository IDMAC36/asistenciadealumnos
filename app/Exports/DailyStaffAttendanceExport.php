<?php

namespace App\Exports;

use App\Models\Staff;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyStaffAttendanceExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected string $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public function array(): array
    {
        $staffMembers = Staff::with(['attendances' => function ($query) {
            $query->whereDate('date', $this->date);
        }])->orderBy('name')->get();

        $rows = [];
        $i = 1;

        foreach ($staffMembers as $member) {
            $attendance = $member->attendances->first();
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
                $member->name,
                $member->dpi,
                $member->role,
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
            'DPI',
            'Rol',
            'Estado',
            'Hora Entrada',
        ];
    }

    public function title(): string
    {
        return 'Asistencia Personal ' . Carbon::parse($this->date)->format('d-m-Y');
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}

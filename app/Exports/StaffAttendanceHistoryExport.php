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

class StaffAttendanceHistoryExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected Staff $staff;
    protected string $month;

    public function __construct(Staff $staff, string $month)
    {
        $this->staff = $staff;
        $this->month = $month;
    }

    public function array(): array
    {
        $startOfMonth = Carbon::parse($this->month . '-01');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now()->startOfDay();

        $attendances = StaffAttendance::where('staff_id', $this->staff->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

        $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $rows = [];

        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            $dateStr = $day->format('Y-m-d');
            $dayOfWeek = $day->dayOfWeek;
            $isWeekend = in_array($dayOfWeek, [0, 6]);
            $isFuture = $day->gt($today);

            if ($isWeekend) {
                continue;
            }

            if ($isFuture) {
                $status = '—';
                $hora = '';
            } elseif (isset($attendances[$dateStr])) {
                $status = ucfirst($attendances[$dateStr]->status);
                $hora = $attendances[$dateStr]->check_in_time
                    ? Carbon::parse($attendances[$dateStr]->check_in_time)->format('h:i A')
                    : '';
            } else {
                $status = 'Sin registro';
                $hora = '';
            }

            $rows[] = [
                $day->format('d/m/Y'),
                $dayNames[$dayOfWeek],
                $status,
                $hora,
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
        ];
    }

    public function title(): string
    {
        return 'Historial ' . $this->staff->name;
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}

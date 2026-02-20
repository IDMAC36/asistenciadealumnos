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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MonthlyAttendanceReportExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected string $month;
    protected array $weekdays = [];
    protected array $rows = [];
    protected array $gradoRows = []; // row indices where grado headers go

    public function __construct(string $month)
    {
        $this->month = $month;
        $this->buildData();
    }

    protected function buildData(): void
    {
        $startOfMonth = Carbon::parse($this->month . '-01');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now()->startOfDay();

        // Weekdays (Mon-Fri)
        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            if (!in_array($day->dayOfWeek, [0, 6])) {
                $this->weekdays[] = $day->copy();
            }
        }

        $students = Student::orderBy('grado')->orderBy('name')->get();
        $grouped = $students->groupBy('grado');

        $attendances = Attendance::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->groupBy('student_id')
            ->map(fn($items) => $items->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d')));

        $permisos = PermissionRequest::where('estado', 'aceptado')
            ->whereNotNull('accepted_at')
            ->whereBetween('created_at', [$startOfMonth->startOfDay(), $endOfMonth->endOfDay()])
            ->get()
            ->groupBy('nombre')
            ->map(fn($items) => $items->keyBy(fn($p) => Carbon::parse($p->created_at)->format('Y-m-d')));

        $rowIndex = 2; // row 1 = headings
        foreach ($grouped as $grado => $studentsInGrado) {
            // Grado separator row
            $gradoRow = array_fill(0, 1 + count($this->weekdays), '');
            $gradoRow[0] = '▸ ' . $grado;
            $this->rows[] = $gradoRow;
            $this->gradoRows[] = $rowIndex;
            $rowIndex++;

            foreach ($studentsInGrado as $student) {
                $row = [$student->name];
                $studentAttendances = $attendances->get($student->id, collect());
                $studentPermisos = $permisos->get($student->name, collect());

                foreach ($this->weekdays as $day) {
                    $dateStr = $day->format('Y-m-d');
                    $isFuture = $day->gt($today);

                    if ($isFuture) {
                        $row[] = '';
                    } elseif ($studentPermisos && $studentPermisos->has($dateStr)) {
                        $row[] = 'Permiso';
                    } elseif ($studentAttendances && $studentAttendances->has($dateStr)) {
                        $status = $studentAttendances->get($dateStr)->status;
                        $row[] = $status === 'presente' ? 'Presente' : 'Ausente';
                    } else {
                        $row[] = 'Sin registro';
                    }
                }

                $this->rows[] = $row;
                $rowIndex++;
            }
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        $heads = ['Nombre'];
        foreach ($this->weekdays as $day) {
            $heads[] = $day->locale('es')->isoFormat('ddd D');
        }
        return $heads;
    }

    public function title(): string
    {
        return 'Reporte ' . Carbon::parse($this->month . '-01')->locale('es')->isoFormat('MMMM YYYY');
    }

    public function styles(Worksheet $sheet): array
    {
        // Auto-size name column
        $sheet->getColumnDimension('A')->setAutoSize(true);

        // Set date columns to width 8
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1 + count($this->weekdays));
        foreach (range('B', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setWidth(10);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalRows = 1 + count($this->rows); // heading + data
                $lastColIndex = 1 + count($this->weekdays);
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

                // Style grado header rows
                foreach ($this->gradoRows as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4338CA'], // indigo-700
                        ],
                    ]);
                }

                // Color-code status cells
                for ($row = 2; $row <= $totalRows; $row++) {
                    if (in_array($row, $this->gradoRows)) continue;

                    for ($col = 2; $col <= $lastColIndex; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $cell = $sheet->getCell("{$colLetter}{$row}");
                        $value = $cell->getValue();

                        $color = match ($value) {
                            'Presente' => '16A34A',   // green-600
                            'Ausente' => 'DC2626',    // red-600
                            'Permiso' => 'F59E0B',    // amber-500
                            'Sin registro' => '9CA3AF', // gray-400
                            default => null,
                        };

                        if ($color) {
                            $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $color],
                                ],
                                'font' => ['color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        }
                    }
                }

                // Borders for all data
                $sheet->getStyle("A1:{$lastCol}{$totalRows}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
